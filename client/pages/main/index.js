//index.js
//获取应用实例
const app = getApp();
let socket = {
  id: null,
  instance: null,
  status: 0, // 0:close, 1:connecting, 2:connected
};

Page({
  data: {
    id: null,
    msgs: [],
    scrollTop: 999999,
  },
  onLoad: function () { },
  onReady: function () {
    // this.connectSocket();
  },
  onShow: function () {
    if (socket.status !== 2) {
      this.connectSocket();
    }
  },
  onHide: function () {
    this.closeSocket();
  },
  closeSocket: function () {
    socket.instance.close();
  },
  connectSocket: function (callback) {
    if (socket.status !== 0) {
      console.log('socket: ' + (socket.status === 1 ? 'connecting' : 'connected'));
      return;
    }
    socket.status = 1;
    var _ = this;
    socket.instance = wx.connectSocket({
      url: 'wss://chat.jiangxinhd.com',
    });
    socket.instance.onOpen(function (res) {
      console.log('socket connected');
      socket.status = 2;
      if (typeof callback === 'function') {
        callback();
      }
    });
    socket.instance.onClose(function () {
      console.log('socket closed');
      socket.instance = null;
      socket.status = 0;
      socket.id = null,
      _.setData({
        msgs: [],
      });
    });
    socket.instance.onError(function (res) {
      console.error(res);
      socket.instance = null;
      socket.status = 0;
      socket.id = null,
      _.setData({
        msgs: [],
      });
    });
    socket.instance.onMessage(function (res) {
      if (socket.id === null && parseInt(res.data) > 0) {
        socket.id = parseInt(res.data);
        _.setData({
          id: socket.id,
        });
      } else {
        var msgs = _.data.msgs.slice(-50);
        msgs.push(JSON.parse(res.data));
        _.setData({
          msgs: msgs,
          scrollTop: _.data.scrollTop + 1,
        });
      }
    });
  },
  sendMsg: function (e) {
    var msg = e.detail.value.msg.trim();
    if (msg) {
      if (socket.status !== 2) {
        this.connectSocket(function () {
          socket.instance.send({
            data: msg,
          });
        });
      } else {
        socket.instance.send({
          data: msg,
        });
      }
    }
  }
})
