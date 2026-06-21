import 'dotenv/config';
import { db, closeDb } from '../src/db/index.js';

const studentId = 'b3b7cb5e-9d9e-4df9-8d2c-7f30c1a8d101';
const suggestions = [
  'Susun portofolio Backend Engineer mulai semester 3',
  'Ikuti proyek atau komunitas yang mendukung fondasi karier',
  'Perkuat dasar algoritma, database, dan komunikasi tim'
];

try {
  await db.query(
    `INSERT INTO recommendations (student_id, suggestions, target_career, score_breakdown)
     VALUES ($1, $2, $3, $4)
     ON CONFLICT (student_id) DO NOTHING`,
    [
      studentId,
      suggestions,
      'Backend Engineer',
      { source: 'demo-seed', phaseCount: 4 }
    ]
  );

  await db.query(
    `INSERT INTO recommendation_history (student_id, suggestions, trigger_event)
     SELECT $1::varchar, $2::text[], 'DemoSeed'
     WHERE NOT EXISTS (
       SELECT 1 FROM recommendation_history WHERE student_id = $1::varchar AND trigger_event = 'DemoSeed'
     )`,
    [studentId, suggestions]
  );

  console.log('Recommendation demo data ready');
} finally {
  await closeDb();
}
