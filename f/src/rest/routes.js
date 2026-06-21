import { Router } from 'express';
import {
  generate,
  getActiveRecommendation,
  getHistory,
  refresh
} from './controllers/recommendationController.js';

export const routes = Router();

routes.get('/api/recommendations/:studentId', getActiveRecommendation);
routes.post('/api/recommendations/generate', generate);
routes.get('/api/recommendations/:studentId/history', getHistory);
routes.post('/internal/recommendations/refresh', refresh);
