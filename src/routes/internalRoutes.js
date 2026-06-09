const express = require('express');
const studentController = require('../controllers/studentController');

const router = express.Router();

router.get('/students/:id/profile', studentController.getInternalProfile);
router.get('/students', studentController.filterStudents);

module.exports = router;
