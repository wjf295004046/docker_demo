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
          <span v-else style="color: red; font-weight: bold">未登录</span>
        </form>
      </div>
    </div>
    <div class="row">
      <div class="col-lg-12" style="" id="chat-window">
      </div>
    </div>
    <div class="row" id="chat-input">
      <div class="col-lg-10">
        <textarea name="content" id="content" rows="5"></textarea>
      </div>
      <div class="col-lg-2">
        <button class="btn btn-success"> 发送</button>
      </div>
    </div>
  </div>

</template>

<script>
export default {
  name: 'Chat',
  data () {
    return {
      nickname: '',
      status: 0
    }
  },
  methods: {
    doLogin: function (event) {
      if (this.nickname !== '') {
        this.status = 1
        this.$refs.nickname.disabled = true
      }
    },
    doLogout: function (event) {
      this.status = 0
      this.$refs.nickname.disabled = false
    }
  }
}
</script>

<style scoped>
  #chat-window {
    margin-top: 20px;
    height: 500px;
    border: 2px solid #ccc;
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
