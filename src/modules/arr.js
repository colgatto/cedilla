const arr = {};
arr.pickRandom = (array) => array[Math.floor(Math.random() * array.length)];

module.exports = arr;