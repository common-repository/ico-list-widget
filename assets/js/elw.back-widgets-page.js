/**
 * ICO List Widget - v1.0.0 - 2017-09-05
 * https://wordpress.org
 *
 * Copyright (c) 2017;
 * Licensed GPLv2+
 */

(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
(function (global){
'use strict';

/**
 * Events Listing Widget
 * https://wordpress.org
 *
 * Licensed under the GPLv2+ license.
 */

window.EventsListingWidget = window.EventsListingWidget || {};

(function (window, document, $, plugin) {
  var $c = {};

  plugin.init = function () {
    plugin.cache();
    plugin.bindEvents();
  };

  plugin.cache = function () {
    $c.window = $(window);
    $c.body = $(document.body);
  };

  plugin.bindEvents = function () {
    /** Initialize wp-color-picker */
    $(document).ready(function () {
      plugin.initColorPicker();
    });

    $(document).ajaxComplete(function (e, xhr, options) {
      if (xhr.responseText) {
        plugin.initColorPicker();
      }
    });
  };

  plugin.initColorPicker = function () {
    $('#widgets-right .elw-color-picker').wpColorPicker();
  };

  $(plugin.init);
})(window, document, (typeof window !== "undefined" ? window['jQuery'] : typeof global !== "undefined" ? global['jQuery'] : null), window.EventsListingWidget);

}).call(this,typeof global !== "undefined" ? global : typeof self !== "undefined" ? self : typeof window !== "undefined" ? window : {})
},{}]},{},[1]);
