import { toast } from 'react-toastify';

function ApiError(message, data, status) {
  let response = null;
  let isObject = false;

  // We are trying to parse response
  try {
    response = JSON.parse(data);
    isObject = true;
  } catch (e) {
    response = data;
  }

  this.response = response;
  this.message = message;
  this.status = status;

  toast.error(`Serveris atgrieza kļūdu: ${this.toString()}`, {autoClose:10000});

  this.toString = function() {
    return `${this.message}\nResponse:\n${
      isObject ? JSON.stringify(this.response, null, 2) : this.response
    }`;
  };
}

export default ApiError;
