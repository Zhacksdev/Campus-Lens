const Student = require('../models/student');

async function getProfile(req, res, next) {
  try {
    const student = await Student.findStudentById(req.params.id);
    if (!student) {
      return res.status(404).json({ message: 'Student not found' });
    }

    const activities = await Student.listActivities(req.params.id);
    return res.json({ ...student, activities });
  } catch (error) {
    return next(error);
  }
}

async function updateProfile(req, res, next) {
  try {
    const student = await Student.updateStudent(req.params.id, {
      name: req.body.name,
      email: req.body.email?.trim().toLowerCase(),
      major: req.body.major,
      semester: req.body.semester ? Number(req.body.semester) : undefined,
      careerGoal: req.body.careerGoal || req.body.career_goal,
    });

    if (!student) {
      return res.status(404).json({ message: 'Student not found' });
    }

    return res.json(student);
  } catch (error) {
    if (error.code === '23505') {
      return res.status(409).json({ message: 'Email is already registered' });
    }

    return next(error);
  }
}

async function getActivities(req, res, next) {
  try {
    const student = await Student.findStudentById(req.params.id);
    if (!student) {
      return res.status(404).json({ message: 'Student not found' });
    }

    const activities = await Student.listActivities(req.params.id);
    return res.json(activities);
  } catch (error) {
    return next(error);
  }
}

async function addActivity(req, res, next) {
  try {
    const { type, name, description, date } = req.body;

    if (!type || !name) {
      return res.status(400).json({ message: 'type and name are required' });
    }

    const student = await Student.findStudentById(req.params.id);
    if (!student) {
      return res.status(404).json({ message: 'Student not found' });
    }

    const activity = await Student.createActivity(req.params.id, { type, name, description, date });
    return res.status(201).json(activity);
  } catch (error) {
    return next(error);
  }
}

async function removeActivity(req, res, next) {
  try {
    const deleted = await Student.deleteActivity(req.params.id, req.params.actId);
    if (!deleted) {
      return res.status(404).json({ message: 'Activity not found' });
    }

    return res.status(204).send();
  } catch (error) {
    return next(error);
  }
}

async function getInternalProfile(req, res, next) {
  try {
    const student = await Student.findStudentById(req.params.id);
    if (!student) {
      return res.status(404).json({ message: 'Student not found' });
    }

    return res.json(student);
  } catch (error) {
    return next(error);
  }
}

async function filterStudents(req, res, next) {
  try {
    const students = await Student.findStudentsByFilter({
      major: req.query.major,
      semester: req.query.semester,
    });

    return res.json(students);
  } catch (error) {
    return next(error);
  }
}

module.exports = {
  getProfile,
  updateProfile,
  getActivities,
  addActivity,
  removeActivity,
  getInternalProfile,
  filterStudents,
};
