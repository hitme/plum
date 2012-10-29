/**
 * @fileoverview php hook实现 
 * @author hanwen<hanwen.sah@taobao.com>
 */
'use strict';
var stdclass = require('../../lib/stdclass');
var path = require('path');
var fs = require('fs');
var spawn = require('child_process').spawn;
var exists = fs.exists || path.exists;

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
    bin: {}
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
    var basePath = this.get('path');

    files.forEach(function(file, i){
      if (file === false) return this._add();

      var filePath = basePath + file;
      exists(filePath, this._do.bind(this, filePath, i));

      return null;

    }, this);

  },

  _add: function(){
    this.set('len', this.get('len') + 1);
  },

  _do: function _do(file, i, exist){

    if (!exist){
      //拒绝处理
      this.fire('reject', {file: file, index: i});
      return this._add();
    }
    //接受处理
    this.fire('receive', {file: file, index: i});

    var phpCmd = this.get('bin')['php'];

    //如果是相对路径
    if (phpCmd.indexOf('.') > -1){
      phpCmd = path.resolve(__dirname, path.dirname('../' + phpCmd)) + 
               '/' + path.basename(phpCmd);
    }

    var basePath = path.dirname(file);

    var cmd = spawn(phpCmd, [file], {cwd: basePath});
    var ret = [];
    var err = [];
    var self = this;

    cmd.stdout.on('data', function cmdSuccess(data){
      self.fire('data', {data: data, index: i});
    });

    cmd.stderr.on('data', function cmdError(err){
      self.fire('data', {data: err, index: i});
    });

    cmd.on('exit', function cmdEnd(){
      self.fire('end', {data: '', index: i});
      self._add();
    });

    return null;
  }

});

module.exports = Hook;
