<template>
  <div class="container">
    <div class="row">
      <div class="col-lg-6">
        <form class="form-inline">
          <div class="form-group">
            <label for="nickname">昵称：</label>
            <input type="text" class="form-control" v-model="nickname" id="nickname" placeholder="请输入昵称">
          </div>
          <button class="btn btn-info" id="login">登录</button>
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
  </div>

</template>

<script>
export default {
  name: 'Chat',
  data () {
    return {
      nickname: '',
      status: 1
    }
  }
}
</script>

<style scoped>

</style>
