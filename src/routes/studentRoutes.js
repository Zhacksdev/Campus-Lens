// Campus Lens user service student routes.
const express = require('express');
const studentController = require('../controllers/studentController');
const { authenticateToken, requireOwnerOrAdmin } = require('../middleware/auth');

const router = express.Router();

router.get('/:id', studentController.getProfile);
router.put('/:id', authenticateToken, requireOwnerOrAdmin, studentController.updateProfile);
router.get('/:id/activities', studentController.getActivities);
router.post('/:id/activities', authenticateToken, requireOwnerOrAdmin, studentController.addActivity);
router.delete('/:id/activities/:actId', authenticateToken, requireOwnerOrAdmin, studentController.removeActivity);

module.exports = router;
