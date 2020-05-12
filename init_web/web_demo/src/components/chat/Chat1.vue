<template>
<el-row :gutter="10">
 <el-col :offset="2" :span="20">
   <el-container>
     <el-header>
       聊天室
     </el-header>
     <el-container>
       <el-aside><div class="">当前在线人数：</div></el-aside>
       <el-container>
         <el-main>
           <el-row>
             <el-col :span="24">
               <div :inline="true" align="left" class="grid-content">
                 昵称：
                 <el-input :disabled="nickname_disable" v-model="nickname" id="nickname" class="input" placeholder="请输入昵称"></el-input>
                 <el-button v-if="status === 0" id="login" v-on:click="doLogin" type="success">登录</el-button>
                 <el-button v-if="status === 1" id="logout" v-on:click="doLogout" type="success">登出</el-button>
                 &nbsp;&nbsp;&nbsp;&nbsp;状态：
                 <span>
                   <i v-if="status === 1" class="el-icon-success text-success"></i>
                   <i v-else-if="status === 2" class="el-icon-success text-danger"></i>
                   <i v-else class="el-icon-success text-black-50"></i>
                 </span>
               </div>
             </el-col>
           </el-row>
           <el-row :gutter="10">
            <el-col :span="24">
              <div id="chat-window" ref="chatWindow" class="d-flex flex-column-reverse">
                <el-row :gutter="10" v-for="(item, index) in chat_record" v-bind:key="index">
                  <el-col class="chat-record-avatar" :span="2" v-if="item.type === 2">
                    <el-avatar>
                      <i class="text-black-50 el-icon-user-solid"></i>
                    </el-avatar>
                  </el-col>
                  <el-col :span="24" v-if="item.type === 1">
                    {{ item.time }} - 系统 - {{ item.msg }}
                  </el-col>
                  <el-col :span="6" :offset="2" class="chat-record-item" v-if="item.type === 2">
                    <span class="chat-item-nickname item-nickname-left">{{ item.nickname }} :</span>
                    <p class="text-left" v-html="item.msg"></p>
                    <span class="float-right chat-item-date item-date-right">{{ item.time }}</span>
                  </el-col>
                  <el-col :span="6" :offset="16" class="chat-record-item bg-white" v-if="item.type === 3">
                    <span class="chat-item-nickname item-nickname-left">{{ item.nickname }} : </span>
                    <p class="text-left" v-html="item.msg"></p>
                    <span class="chat-item-date item-date-right">{{ item.time }}</span>
                  </el-col>
                  <el-col :span="24" v-if="item.type === -1">
                    {{ item.msg }}
                  </el-col>
                  <el-col class="chat-record-avatar" :span="2" v-if="item.type === 3">
                    <el-avatar>
                      <i class="text-black-50 el-icon-user-solid"></i>
                    </el-avatar>
                  </el-col>
                </el-row>
              </div>
            </el-col>
           </el-row>
           <el-row :gutter="10">
            <el-col class="new-message" v-if="new_record > 0" :span="24">
                <a href="javascript:void(0)" v-on:click="scrollToBottom">您有 {{ new_record }} 条新消息</a>
            </el-col>
           </el-row>
           <el-row :gutter="10" id="send-window">
            <el-col :span="18">
              <el-input type="textarea" placeholder="请输入聊天内容" name="content" id="content" rows="5" v-model="msg"></el-input>
            </el-col>
             <el-col :span="6">
               <el-button type="success" v-on:click="sendMsg">发送</el-button>
             </el-col>
           </el-row>
         </el-main>
       </el-container>
     </el-container>
   </el-container>
 </el-col>
</el-row>
</template>

<script>
import constant from '../../../common/constant'

export default {
  name: 'Chat1',
  data () {
    return {
      nickname: '',
      msg: '',
      status: 0,
      websocket: null,
      chat_record: [],
      new_record: 0,
      chat_window_client_height: 0,
      nickname_disable: false
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
      // console.log(this.$refs.nickname.setAttribute('disabled', true))
      if (this.nickname !== '') {
        this.websocket = this.connect_websocket(this.nickname)
        this.status = 1
        this.nickname_disable = true
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
      // this.chat_record.push(data)
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

  .chat-record-item p {
    padding-left: 1rem;
    padding-right: 1rem;
    word-break: break-all;
  }
  .chat-record-item {
    background-color: lightgreen;
    position: relative;
    border-radius: 1rem;
    border: 1px solid #ccc;
    box-shadow: 0 2px 4px rgba(0, 0, 0, .12), 0 0 6px rgba(0, 0, 0, .04);
    padding-top: 1rem;
    margin-top: 2rem;
    margin-bottom: 0.5rem;
  }
  .el-input {
    width: 200px;
  }
  #chat-window {
    margin-top: 1.5rem;
    border: 1px solid #ccc;
    width: 100%;
    height: 30rem;
    overflow-y: auto;
    overflow-x: hidden;
    background-color: #EBEEF5;
  }
  .chat-record-avatar {
    padding-top: 2rem;
  }
  .new-message {
    /*background-color: #909399;*/
    padding-top: 0.5rem;
    padding-bottom: 0.5rem;
    float: outside;
    margin-top: -2rem;

  }

  #send-window {
    margin-top: 1rem;
  }
  #send-window .el-button {
    padding: 2rem;
    margin-top: 1rem;
  }
</style>
