'use strict';
var stdclass = require('../../lib/stdclass');
var path = require('path');
var fs = require('fs');
var exists = fs.exists || path.exists;
var URL      = require('url');
var webx = require('./webx');

function Hook(){
  this.init.apply(this, arguments);
}

stdclass.extend(Hook, stdclass, {

 attributes: {
    path: '',
    files: [],
    len: 0,
    initialized: true
  },

  CONSIT: {
    request: {}
  },

  _init: function init(){
    this._bind();
  },

  _bind: function bind(){
    this.on('change:initialized', function(e){
      if (e.now) this.parse();
    });
  },

  parse: function parse(){

    if (!this.get('initialized')) return;

    var files = this.get('files');

    files.forEach(function(file, i){

      if (file === false) return this._add();

      var basePath = this.get('path');

      var customs = this.get('customs');
      var basename = path.basename(file).replace(/\.html{0,1}$/, '');

      if (customs && customs.prefix) {

        customs.prefix.some(function(p){

          if (p.indexOf(file)) {
            file = file.slice(p.length);
            basePath = basePath + p + '/';
            return true;
          }

        });

      }

      var filePath = basePath + 'screen' + path.dirname(file) + '/' + basename + '.vm';
      exists(filePath, this._do.bind(this, file, filePath, basePath, i));
      return null;
    }, this);

  },

  _add: function(){
    this.set('len', this.get('len') + 1);
  },

  _do: function _do(file, filePath, basePath, i, exist){

    if (!exist){
      //拒绝处理
      this.fire('reject', {file: file, index: i});
      this._add();
      return;
    }

    var request = this.get('request');
    var url = URL.parse(request.url, true);
    var isParse = '__parse' in url.query;
    var isJsonify = 'jsonify' in url.query;

    //接受处理
    this.fire('receive', {file: file, index: i});
    this.fire('set:header', {headers: {'Content-Type': "text/html; charset=gbk"}});
    this._add();

    if (isParse) {
      this.fire('set:header', {type: '.json'});
    }

    //try {
    var str = (new webx({
      filePath: filePath,
      isParse: isParse,
      isJsonify: isJsonify,
      basePath: basePath,
      file : file,
      rundata: { parameters: url.query }
    })).parse();
    this.fire('end', {index: i, data: str });
    //} catch(e) {
    //throw e;
    //this.fire('end', {index: i, data: '<pre>' + e.toString()});
    //}
  }

});

module.exports = Hook;
