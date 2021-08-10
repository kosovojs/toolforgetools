const isDev = process.env.NODE_ENV === 'development';

export const API_URL = '//edgars.toolforge.org/rlr/api.php';//isDev ? '//tools.wmflabs.org/edgars/npp2/api/index.php' : '//tools.wmflabs.org/assessor/api/index.php';
