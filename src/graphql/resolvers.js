import {
  getRecommendation,
  getRecommendationHistory,
  upsertRecommendation
} from '../services/recommendationService.js';

export const resolvers = {
  Query: {
    recommendationsByStudent: async (_, { studentId }) => getRecommendation(studentId),
    recommendationHistory: async (_, { studentId, limit = 10 }) =>
      getRecommendationHistory(studentId, limit)
  },
  Mutation: {
    updateRecommendation: async (_, { studentId, suggestions }) =>
      upsertRecommendation({
        studentId,
        suggestions,
        triggerEvent: 'GraphQLMutation'
      })
  }
};
