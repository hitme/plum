"use strict";
var utils = {
  noop: function noop(){
    var arg = arguments;
    return function(){ return arg; };
  }
};

['forEach', 'some', 'every', 'filter', 'map'].forEach(function(fnName){
  utils[fnName] = function(arr, fn, context){
    if (!arr || typeof arr == 'string') return arr;
    context = context || this;
    if (arr[fnName]){
      return arr[fnName](fn, context);
    } else {
      var keys = Object.keys(arr);
      return keys[fnName](function(key){
        return fn.call(context, arr[key], key, arr);
      }, context);
    }
  };
});

var num = 0;
utils.guid = function(){
  return num++;
};

utils.mixin = function (to, from){
  utils.forEach(from, function(val, key){
    var toString = {}.toString.call(val);
    if (toString == '[object Array]' || toString == '[object Object]') {
      to[key] = utils.mixin(val, to[key] || {});
    } else {
      to[key] = val;
    }
  });
  return to;
};

utils.isArray = function(obj){
  return {}.toString.call(obj) === '[object Array]';
};

module.exports = utils;
