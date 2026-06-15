// Campus Lens user service student data access layer.
const db = require('../config/db');

const publicStudentFields = `
  id,
  name,
  email,
  major,
  semester,
  career_goal,
  role,
  created_at,
  updated_at
`;

async function createStudent({ name, email, passwordHash, major, semester, careerGoal, role = 'student' }) {
  const result = await db.query(
    `INSERT INTO students (name, email, password, major, semester, career_goal, role)
     VALUES ($1, $2, $3, $4, $5, $6, $7)
     RETURNING ${publicStudentFields}`,
    [name, email, passwordHash, major, semester || 1, careerGoal || null, role]
  );

  return result.rows[0];
}

async function findStudentByEmail(email) {
  const result = await db.query('SELECT * FROM students WHERE email = $1', [email]);
  return result.rows[0];
}

async function findStudentById(id) {
  const result = await db.query(`SELECT ${publicStudentFields} FROM students WHERE id = $1`, [id]);
  return result.rows[0];
}

async function updateStudent(id, fields) {
  const allowedFields = {
    name: 'name',
    email: 'email',
    major: 'major',
    semester: 'semester',
    careerGoal: 'career_goal',
  };

  const entries = Object.entries(fields).filter(
    ([key, value]) => allowedFields[key] && value !== undefined
  );

  if (entries.length === 0) {
    return findStudentById(id);
  }

  const setClause = entries.map(([key], index) => `${allowedFields[key]} = $${index + 2}`).join(', ');
  const values = entries.map(([, value]) => value);

  const result = await db.query(
    `UPDATE students SET ${setClause} WHERE id = $1 RETURNING ${publicStudentFields}`,
    [id, ...values]
  );

  return result.rows[0];
}

async function listActivities(studentId) {
  const result = await db.query(
    `SELECT id, student_id, type, name, description, date, created_at
     FROM student_activities
     WHERE student_id = $1
     ORDER BY COALESCE(date, created_at::date) DESC, created_at DESC`,
    [studentId]
  );

  return result.rows;
}

async function createActivity(studentId, { type, name, description, date }) {
  const result = await db.query(
    `INSERT INTO student_activities (student_id, type, name, description, date)
     VALUES ($1, $2, $3, $4, $5)
     RETURNING id, student_id, type, name, description, date, created_at`,
    [studentId, type, name, description || null, date || null]
  );

  return result.rows[0];
}

async function deleteActivity(studentId, activityId) {
  const result = await db.query(
    `DELETE FROM student_activities
     WHERE id = $1 AND student_id = $2
     RETURNING id`,
    [activityId, studentId]
  );

  return result.rowCount > 0;
}

async function findStudentsByFilter({ major, semester }) {
  const params = [];
  const where = [];

  if (major) {
    params.push(major);
    where.push(`major = $${params.length}`);
  }

  if (semester) {
    params.push(Number(semester));
    where.push(`semester = $${params.length}`);
  }

  const result = await db.query(
    `SELECT ${publicStudentFields}
     FROM students
     ${where.length ? `WHERE ${where.join(' AND ')}` : ''}
     ORDER BY created_at DESC`,
    params
  );

  return result.rows;
}

module.exports = {
  createStudent,
  findStudentByEmail,
  findStudentById,
  updateStudent,
  listActivities,
  createActivity,
  deleteActivity,
  findStudentsByFilter,
};
