<template>
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <form class="form-inline">
          <div class="form-group">
            <label for="nickname">昵称：</label>
            <input type="text" class="form-control" ref="nickname" v-model="nickname" id="nickname" placeholder="请输入昵称">
          </div>
          <button class="btn btn-info" id="login" v-if="status === 0" v-on:click="doLogin">登录</button>
          <button class="btn btn-info" id="logout" v-if="status === 1" v-on:click="doLogout">登出</button>
          &nbsp;&nbsp;&nbsp;&nbsp;状态：
          <span v-if="status === 1" style="color: green; font-weight: bold">已登录</span>
          <span v-else-if="status === 2" style="color: red; font-weight: bold">登录失败</span>
          <span v-else style="color: red; font-weight: bold">未登录</span>
        </form>
      </div>
    </div>
    <div class="row">
      <div class="container-fluid d-flex flex-column-reverse" style="" id="chat-window" ref = 'chatWindow'>
        <div class="row" v-for="(item, index) in chat_record" v-bind:key="index" >
          <div class="col-lg-12" v-if="item.type === 1">
            {{ item.time }} - 系统 - {{ item.msg }}
          </div>
          <div class="col-lg-3 offset-lg-1 chat-record-item bg-white" v-if="item.type === 2">
            <span class="chat-item-nickname item-nickname-left">{{ item.nickname }} : </span>
            <p class="text-left" v-html="item.msg"></p>
            <span class="float-right chat-item-date item-date-right">{{ item.time }}</span>
          </div>
          <div class="col-lg-3 offset-lg-8 chat-record-item" v-if="item.type === 3">
            <span class="chat-item-nickname item-nickname-left">{{ item.nickname }} : </span>
            <p class="text-left" v-html="item.msg"></p>
            <span class="chat-item-date item-date-right">{{ item.time }}</span>
          </div>
          <div class="col-lg-12" v-if="item.type === -1">
            {{ item.msg }}
          </div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="new-message" v-if="new_record > 0">
        <a href="javascript:void(0)" v-on:click="scrollToBottom">您有 {{ new_record }} 条新消息</a>
      </div>
    </div>
    <div class="row" id="chat-input">
      <div class="col-lg-10">
        <textarea name="content" id="content" rows="5" v-model="msg"></textarea>
      </div>
      <div class="col-lg-2">
        <button class="btn btn-success" v-on:click="sendMsg"> 发送</button>
      </div>
    </div>
  </div>

</template>

<script>
import constant from '../../../common/constant'

export default {
  name: 'Chat',
  data () {
    return {
      nickname: '',
      msg: '',
      status: 0,
      websocket: null,
      chat_record: [],
      new_record: 0,
      chat_window_client_height: 0
    }
  },
  created: function () {
    this.listenScroll()
  },
  watch: {
    chat_record: function () {
      if (this.$refs.chatWindow.clientHeight + this.$refs.chatWindow.scrollTop !== this.$refs.chatWindow.scrollHeight) {
        this.new_record++
      }
      // this.$refs.chatWindow.scrollTop = this.$refs.chatWindow.scrollHeight
    }
  },
  methods: {
    scrollToBottom: function (e) {
      let chatWindow = document.getElementById('chat-window')
      // this.$refs.chatWindow.scrollTop = this.$refs.chatWindow.scrollHeight
      chatWindow.scrollTop = chatWindow.scrollHeight
    },
    listenScroll: function (e) {
      document.addEventListener('scroll', this.handleScroll, true)
    },
    handleScroll: function (e) {
      let chatWindow = document.getElementById('chat-window')
      if (chatWindow.clientHeight + chatWindow.scrollTop === chatWindow.scrollHeight) {
        this.new_record = 0
      }
    },
    sendMsg: function (event) {
      if (this.status === 1 && this.msg !== '') {
        let data = {
          msg: this.msg,
          nickname: this.nickname
        }
        console.log(JSON.stringify(data))
        this.websocket.send(JSON.stringify(data))
      }
    },
    doLogin: function (event) {
      if (this.nickname !== '') {
        this.websocket = this.connect_websocket(this.nickname)
        this.status = 1
        this.$refs.nickname.disabled = true
      }
    },
    doLogout: function (event) {
      this.websocket.close()
      this.websocket = null
    },
    connect_websocket: function (nickname) {
      let WsServer = constant.WEBSOCKET_HOST
      let websocket = new WebSocket(WsServer + '?nickname=' + nickname)
      websocket.onopen = function (evt) {
        console.log('Connected to WebSocket server.')
      }
      websocket.onclose = this.websocket_onclose
      websocket.onmessage = this.websocket_onmsg
      websocket.onerror = function (evt, e) {
        console.log('Error occured: ' + evt.data)
      }
      return websocket
    },
    websocket_onmsg: function (evt) {
      let data = JSON.parse(evt.data)
      data.msg = data.msg.replace('\n', '<br />')
      this.chat_record.unshift(data)
      console.log(data)
    },
    websocket_onclose: function (evt) {
      this.status = 0
      this.$refs.nickname.disabled = false
      console.log('Disconnected')
      let msg = {
        type: -1,
        msg: '您已退出群聊'
      }
      this.chat_record.unshift(msg)
    }
  }
}
</script>

<style scoped>
  .new-message {
    position: absolute;
    /*bottom: 0.1rem;*/
    width: 100%;
    float: left;
    margin-top: -2rem;
  }
  .chat-item-nickname {
    font-weight: bold;
    font-size: 0.5rem;
    position: absolute;
  }
  .item-nickname-left {
    left: 0.5rem;
    top: 0.2rem;
  }
  .chat-item-date {
    font-size: 0.5rem;
    position: absolute;
  }
  .item-date-right {
    right: 0.5rem;
    bottom: 0.3rem;
  }
  .chat-record-item {
    background-color: lightgreen;
    border-radius: 10px;
    padding-top: 1rem;
    margin-top: 2rem;
  }
  .chat-record-item p {
    word-break: break-all;
  }

  #chat-window {
    margin-top: 20px;
    height: 500px;
    border: 2px solid #ccc;
    background-color: lightgrey;
    overflow-y: auto;
  }

  #chat-input {
    margin-top: 10px;
  }

  #chat-input textarea {
    width: 100%;
    height: 100px;
  }

  #chat-input button {
    margin-top: 20px;
    width: 70%;
    height: 50px;
  }
</style>
