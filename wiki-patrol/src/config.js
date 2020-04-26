const isDev = process.env.NODE_ENV === 'development';

export const API_URL = '//tools.wmflabs.org/edgars/wiki-patrol/save.php';//isDev ? '//tools.wmflabs.org/edgars/npp2/api/index.php' : '//tools.wmflabs.org/assessor/api/index.php';
