const obj = {};

/**
 * Recursively copy the values of all of the enumerable own properties from one or more source objects to a target object. 
 * @param {object} object The target object to copy to.
 * @param {object} toassign The object to copy.
 * @return {object} The target object.
 */
obj.recAssign = ( object, ...toassign ) => {
	const isPlainObject = o => o !== null && typeof o !== 'undefined' && typeof o.constructor !== 'undefined' && o.constructor.prototype === Object.prototype;
	const assign = ( ref, key, value ) => {
		if( isPlainObject(value) ){
			if( !isPlainObject(ref[key]) ){
				ref[key] = {};
			}
			mergeInObject( ref[key], value );
		}else{
			ref[key] = value;
		}
	};
	const mergeInObject = ( dest, data ) => {
		Object.keys( data ).forEach( key => {
			assign( dest, key, data[key] );
		});
	};
	if( typeof object === 'object' ){
		toassign.forEach( data => {
			if( isPlainObject(data) ){
				mergeInObject( object, data );
			}
		});
	}
	return object;
};

/**
 * Recursively trasform object into array of {key, value} object
 * @param {object} data The starting object.
 * @return {array} The final array.
 */
obj.flatObj = (data) => {
	let keys = Object.keys(data);
	let plain = [];
	for (let i = 0; i < keys.length; i++) {
		const k = keys[i];
		const v = data[k];
		if(v === null || typeof v != 'object'){
			plain.push({key: k, value: v});
		}else{
			if(Object.keys(v).length == 0){
				plain.push({key: k, value: null});
			}else{
				plain.push(...obj.flatObj(v));
			}
		}
	}
	return plain;
};

/**
 * Return the object with object.key equals to value searched
 * @param {object[]} objList array of object to search into.
 * @param {string} key The key to use for match.
 * @param {any} value The value searched.
 * @return {any} The return value.
 */
obj.getBy = (objList, key, value) => {
	for (let i = 0; i < objList.length; i++) {
		if(objList[i][key] === value)
			return objList[i];
	}
};

/**
 * Return the index of object with object.key equals to value searched, -1 if not found
 * @param {object[]} objList array of object to search into.
 * @param {string} key The key to use for match.
 * @param {any} value The value searched.
 * @return {int} The index found.
 */
obj.getIndexBy = (objList, key, value) => {
	for (let i = 0; i < objList.length; i++) {
		if(objList[i][key] == value)
			return i;
	}
	return -1;
};

module.exports = obj;