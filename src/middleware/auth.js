// Campus Lens user service JWT authentication middleware.
const jwt = require('jsonwebtoken');

function authenticateToken(req, res, next) {
  const authHeader = req.headers.authorization;
  const token = authHeader && authHeader.startsWith('Bearer ') ? authHeader.slice(7) : null;

  if (!token) {
    return res.status(401).json({ message: 'Authentication token is required' });
  }

  try {
    req.user = jwt.verify(token, process.env.JWT_SECRET);
    return next();
  } catch (error) {
    return res.status(401).json({ message: 'Invalid or expired token' });
  }
}

function requireOwnerOrAdmin(req, res, next) {
  if (req.user?.role === 'admin' || req.user?.id === req.params.id) {
    return next();
  }

  return res.status(403).json({ message: 'You can only access your own profile' });
}

module.exports = {
  authenticateToken,
  requireOwnerOrAdmin,
};
