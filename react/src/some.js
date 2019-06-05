'use strict';

let sum = function(numbers) {
    let sum = 0;

    for(let i = 0, l = numbers.length; i < l; i++) {
        sum += numbers[i];
    };

    return sum;
};

let avg = function(numbers) {
    let avg = sum(numbers) / numbers.length;

    return avg;
};

let greet = function(name) {

    return 'Hello ' + name;
};

export default avg;