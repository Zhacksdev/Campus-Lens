import {
  generateRecommendation,
  getRecommendation,
  getRecommendationHistory,
  refreshFromEvent
} from '../../services/recommendationService.js';

export async function getActiveRecommendation(req, res, next) {
  try {
    const recommendation = await getRecommendation(req.params.studentId);

    if (!recommendation) {
      return res.status(404).json({ message: 'Recommendation not found' });
    }

    return res.json(recommendation);
  } catch (error) {
    return next(error);
  }
}

export async function generate(req, res, next) {
  try {
    const studentId = req.body.student_id || req.body.studentId;

    if (!studentId) {
      return res.status(400).json({ message: 'student_id is required' });
    }

    const recommendation = await generateRecommendation(studentId);
    return res.status(201).json(recommendation);
  } catch (error) {
    return next(error);
  }
}

export async function getHistory(req, res, next) {
  try {
    const limit = Number(req.query.limit) || 10;
    const history = await getRecommendationHistory(req.params.studentId, limit);
    return res.json(history);
  } catch (error) {
    return next(error);
  }
}

export async function refresh(req, res, next) {
  try {
    const event = req.body.event || 'InternalRefresh';
    const recommendation = await refreshFromEvent(event, req.body);
    return res.json(recommendation);
  } catch (error) {
    return next(error);
  }
}
