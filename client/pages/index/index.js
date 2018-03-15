//index.js
//获取应用实例
const app = getApp();

var id = null;
var socket = null;

Page({
  data: {
    msgs: [
      // {
      //   isMe: 1,
      //   msg: "aa"
      // }, {
      //   isMe: 0,
      //   msg: 'b'
      // }
    ],
  },
  onLoad: function () {
  },
  onReady: function () {
    var _ = this;

    socket = wx.connectSocket({
      // url: 'wss://wechat.xizhiqm.cn',
      url: 'ws://192.168.0.10:443',
      // method: 'CONNECT',
    });
    socket.onMessage(function (res) {
      if (id === null) {
        id = res.data;
        console.log('my: ' + id);
      } else {
        var msgObj = JSON.parse(res.data);
        var msgs = _.data.msgs;
        msgs.push({
          isMe: msgObj.id == id,
          msg: msgObj.msg
        });
        _.setData({
          msgs: msgs,
        });
      }
    });
  },
  sendMsg: function (e) {
    var msg = e.detail.value.msg.trim();
    if (msg) {
      socket.send({
        data: msg,
      });
    }
  },
})
