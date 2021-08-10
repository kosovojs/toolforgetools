var last_update = '2018. gada 10. jūnijā';

Array.prototype.unique = function() {
  return this.filter(function (value, index, self) { 
    return self.indexOf(value) === index;
  });
}

function push_to_arr(arr,key,val) {
	if (key in arr) {
		arr[key].push(val);
	} else {
		arr[key] = [val];
	}
	
	return arr
}