export const typeDefs = `#graphql
  type Query {
    recommendationsByStudent(studentId: ID!): Recommendation
    recommendationHistory(studentId: ID!, limit: Int): [RecommendationRecord!]!
  }

  type Recommendation {
    id: ID!
    studentId: ID!
    suggestions: [String!]!
    targetCareer: String
    updatedAt: String!
  }

  type RecommendationRecord {
    id: ID!
    suggestions: [String!]!
    triggerEvent: String
    createdAt: String!
  }

  type Mutation {
    updateRecommendation(studentId: ID!, suggestions: [String!]!): Recommendation!
  }
`;
