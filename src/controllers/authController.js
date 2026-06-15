const bcrypt = require('bcryptjs');
const jwt = require('jsonwebtoken');
const Student = require('../models/student');

function buildToken(student) {
  return jwt.sign(
    {
      id: student.id,
      role: student.role,
      major: student.major,
      semester: student.semester,
    },
    process.env.JWT_SECRET,
    { expiresIn: process.env.JWT_EXPIRES_IN || '7d' }
  );
}

function sanitizeRegisterBody(body) {
  return {
    name: body.name?.trim(),
    email: body.email?.trim().toLowerCase(),
    password: body.password,
    major: body.major?.trim(),
    semester: body.semester ? Number(body.semester) : 1,
    careerGoal: body.careerGoal || body.career_goal,
  };
}

async function register(req, res, next) {
  try {
    const data = sanitizeRegisterBody(req.body);

    if (!data.name || !data.email || !data.password || !data.major) {
      return res.status(400).json({ message: 'name, email, password, and major are required' });
    }

    if (!Number.isInteger(data.semester) || data.semester < 1) {
      return res.status(400).json({ message: 'semester must be a positive integer' });
    }

    const existingStudent = await Student.findStudentByEmail(data.email);
    if (existingStudent) {
      return res.status(409).json({ message: 'Email is already registered' });
    }

    const passwordHash = await bcrypt.hash(data.password, 10);
    const student = await Student.createStudent({ ...data, passwordHash });
    const token = buildToken(student);

    return res.status(201).json({ token, student });
  } catch (error) {
    return next(error);
  }
}

async function login(req, res, next) {
  try {
    const email = req.body.email?.trim().toLowerCase();
    const { password } = req.body;

    if (!email || !password) {
      return res.status(400).json({ message: 'email and password are required' });
    }

    const student = await Student.findStudentByEmail(email);
    if (!student) {
      return res.status(401).json({ message: 'Invalid email or password' });
    }

    const isPasswordValid = await bcrypt.compare(password, student.password);
    if (!isPasswordValid) {
      return res.status(401).json({ message: 'Invalid email or password' });
    }

    const token = buildToken(student);
    const { password: _password, ...studentWithoutPassword } = student;

    return res.json({ token, student: studentWithoutPassword });
  } catch (error) {
    return next(error);
  }
}

function logout(_req, res) {
  return res.json({ message: 'Logout successful. Remove the token on the client side.' });
}

module.exports = {
  register,
  login,
  logout,
};
