// Idempotent demo data for local and Docker environments.
require('dotenv').config();

const bcrypt = require('bcryptjs');
const { pool } = require('../config/db');

async function seed() {
  const password = await bcrypt.hash('password123', 10);
  const student = await pool.query(
    `INSERT INTO students (id, name, email, password, major, semester, career_goal, role)
     VALUES (
       'b3b7cb5e-9d9e-4df9-8d2c-7f30c1a8d101',
       'Alya Pratama',
       'alya@campuslens.test',
       $1,
       'Informatics',
       3,
       'Backend Engineer',
       'student'
     )
     ON CONFLICT (email) DO NOTHING
     RETURNING id`,
    [password]
  );

  const studentId = student.rows[0]?.id || 'b3b7cb5e-9d9e-4df9-8d2c-7f30c1a8d101';

  await pool.query(
    `INSERT INTO student_activities (student_id, type, name, description, date)
     SELECT $1, 'organization', 'Campus Lens Backend Club', 'Build API and database projects.', '2026-06-16'
     WHERE NOT EXISTS (
       SELECT 1 FROM student_activities WHERE student_id = $1 AND name = 'Campus Lens Backend Club'
     )`,
    [studentId]
  );

  console.log('User demo data ready');
  await pool.end();
}

seed().catch(async (error) => {
  console.error('User demo seed failed', error);
  await pool.end();
  process.exit(1);
});
