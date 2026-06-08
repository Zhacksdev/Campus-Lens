import axios from 'axios';

const timeout = 3000;

export async function getStudentProfile(studentId) {
  const baseUrl = process.env.USER_SERVICE_URL;

  if (!baseUrl) {
    return null;
  }

  try {
    const response = await axios.get(`${baseUrl}/internal/students/${studentId}/profile`, { timeout });
    return response.data;
  } catch (error) {
    console.warn(`User Service unavailable for student ${studentId}: ${error.message}`);
    return null;
  }
}

export async function getCareerPhases() {
  const baseUrl = process.env.CAREER_SERVICE_URL;

  if (!baseUrl) {
    return [];
  }

  try {
    const response = await axios.get(`${baseUrl}/internal/roadmap/phases`, { timeout });
    return Array.isArray(response.data) ? response.data : response.data?.phases || [];
  } catch (error) {
    console.warn(`Career Roadmap Service unavailable: ${error.message}`);
    return [];
  }
}
