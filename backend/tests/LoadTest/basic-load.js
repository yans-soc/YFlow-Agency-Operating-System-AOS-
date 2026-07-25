import http from 'k6/http';
import { check, sleep } from 'k6';

export const options = {
  vus: 5,
  duration: '10s',
};

const BASE_URL = __ENV.BASE_URL || 'http://localhost:8000';

export default function () {
  // Test public endpoints
  let res = http.get(`${BASE_URL}/api/v1/version/current`);
  check(res, {
    'version endpoint status 200 or 404': (r) => r.status === 200 || r.status === 404,
  });
  sleep(1);
}