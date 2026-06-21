CREATE EXTENSION IF NOT EXISTS "pgcrypto";

CREATE TABLE IF NOT EXISTS recommendations (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  student_id      VARCHAR(50) UNIQUE NOT NULL,
  suggestions     TEXT[] NOT NULL,
  target_career   VARCHAR(100),
  score_breakdown JSONB,
  updated_at      TIMESTAMP DEFAULT NOW(),
  created_at      TIMESTAMP DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS recommendation_history (
  id              UUID PRIMARY KEY DEFAULT gen_random_uuid(),
  student_id      VARCHAR(50) NOT NULL,
  suggestions     TEXT[] NOT NULL,
  trigger_event   VARCHAR(100),
  created_at      TIMESTAMP DEFAULT NOW()
);

CREATE INDEX IF NOT EXISTS idx_rec_student ON recommendations(student_id);
CREATE INDEX IF NOT EXISTS idx_rh_student ON recommendation_history(student_id);
