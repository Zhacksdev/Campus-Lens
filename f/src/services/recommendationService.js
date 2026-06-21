import { db } from '../db/index.js';
import { publishRecommendationUpdated } from '../messaging/publisher.js';
import { getCareerPhases, getStudentProfile } from './httpClient.js';

function mapRecommendation(row) {
  if (!row) {
    return null;
  }

  return {
    id: row.id,
    studentId: row.student_id,
    suggestions: row.suggestions,
    targetCareer: row.target_career,
    scoreBreakdown: row.score_breakdown,
    updatedAt: row.updated_at,
    createdAt: row.created_at
  };
}

function mapHistory(row) {
  return {
    id: row.id,
    studentId: row.student_id,
    suggestions: row.suggestions,
    triggerEvent: row.trigger_event,
    createdAt: row.created_at
  };
}

function buildSuggestions({ profile, phases, triggerEvent }) {
  const semester = Number(profile?.semester) || 1;
  const targetCareer = profile?.target_career || profile?.targetCareer || 'Backend Engineer';
  const activities = Array.isArray(profile?.activities) ? profile.activities : [];
  const phaseTitle = phases[0]?.title || phases[0]?.name || 'fondasi karier';

  const suggestions = [
    `Susun portofolio ${targetCareer} mulai semester ${semester}`,
    `Ikuti proyek atau komunitas yang mendukung ${phaseTitle}`,
    semester < 5
      ? 'Perkuat dasar algoritma, database, dan komunikasi tim'
      : 'Prioritaskan magang, sertifikasi, dan studi kasus industri'
  ];

  if (activities.length === 0) {
    suggestions.push('Tambahkan satu aktivitas kampus untuk memperluas jejaring');
  }

  if (triggerEvent === 'DifficultyScoreUpdated') {
    suggestions.push('Tinjau ulang rencana mata kuliah berdasarkan tingkat kesulitan terbaru');
  }

  return { suggestions, targetCareer };
}

export async function getRecommendation(studentId) {
  const result = await db.query('SELECT * FROM recommendations WHERE student_id = $1', [studentId]);
  return mapRecommendation(result.rows[0]);
}

export async function getRecommendationHistory(studentId, limit = 10) {
  const result = await db.query(
    'SELECT * FROM recommendation_history WHERE student_id = $1 ORDER BY created_at DESC LIMIT $2',
    [studentId, limit]
  );
  return result.rows.map(mapHistory);
}

export async function upsertRecommendation({
  studentId,
  suggestions,
  targetCareer = null,
  scoreBreakdown = {},
  triggerEvent = 'ManualUpdate',
  shouldPublish = true
}) {
  const client = await db.connect();

  try {
    await client.query('BEGIN');

    const result = await client.query(
      `INSERT INTO recommendations (student_id, suggestions, target_career, score_breakdown)
       VALUES ($1, $2, $3, $4)
       ON CONFLICT (student_id) DO UPDATE SET
         suggestions = EXCLUDED.suggestions,
         target_career = COALESCE(EXCLUDED.target_career, recommendations.target_career),
         score_breakdown = EXCLUDED.score_breakdown,
         updated_at = NOW()
       RETURNING *`,
      [studentId, suggestions, targetCareer, scoreBreakdown]
    );

    await client.query(
      `INSERT INTO recommendation_history (student_id, suggestions, trigger_event)
       VALUES ($1, $2, $3)`,
      [studentId, suggestions, triggerEvent]
    );

    await client.query('COMMIT');

    const recommendation = mapRecommendation(result.rows[0]);

    if (shouldPublish) {
      await publishRecommendationUpdated({
        studentId,
        suggestions,
        targetCareer: recommendation.targetCareer,
        triggerEvent
      });
    }

    return recommendation;
  } catch (error) {
    await client.query('ROLLBACK');
    throw error;
  } finally {
    client.release();
  }
}

export async function generateRecommendation(studentId, triggerEvent = 'ManualGenerate') {
  const [profile, phases] = await Promise.all([
    getStudentProfile(studentId),
    getCareerPhases()
  ]);
  const { suggestions, targetCareer } = buildSuggestions({ profile, phases, triggerEvent });

  return upsertRecommendation({
    studentId,
    suggestions,
    targetCareer,
    scoreBreakdown: {
      source: profile ? 'profile-and-roadmap' : 'fallback',
      phaseCount: phases.length,
      generatedAt: new Date().toISOString()
    },
    triggerEvent
  });
}

export async function refreshFromEvent(eventName, payload = {}) {
  const data = payload.data || payload;
  const studentId = data.student_id || data.studentId;

  if (!studentId) {
    throw new Error(`Event ${eventName} missing student_id`);
  }

  return generateRecommendation(studentId, eventName);
}
