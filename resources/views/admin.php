<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>ADMIN PANEL</title>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="/css/grid.css">
    <link rel="stylesheet" href="/css/table.css">
    <style media="screen">
      * {
        font-family: monospace;
      }
      body {
        background-color: black;
        color: green;
        font-size: 18px;
        font-family: Monospace;
      }
      .form--mantap {
        background-color: black;
        border: 0px;
        border-bottom: 1px solid green;
        color: green;
        font-size: 20px;
      }
      .form--mantap:focus {
        outline: none;
      }
      input[type="password"] {
        color: #ff00006b;
      }
      a {
        color: green;
        text-decoration: underline;
      }
      a:focus, a:hover {
        color: green;
        outline: none;
      }
      pre {
        font-family: Monospace;
      }
      .btn--mantap {
        color: red;
        background-color: #00000000;
        border: 0px;
      }
      .btn--mantap:focus {
        outline: none;
      }
      .redirect {
        margin: 30px;
      }
    </style>
  </head>
  <body>
    <div id="app">
      <div v-if="isLogged">
        <div class="col-md-12">
          <div v-if="isLoading">
            Fetching....
          </div>
          <div v-else>
            <div style="padding: 10px;">
              <span>Billing: {{ data.billing }}</span>
              <span> | </span>
              <span>Card: {{ data.card }}</span>
            </div>
            <table class="table table-bordered">
              <tbody>
                <tr v-for="(t, index) in data.login" :key="t.id">
                  <td align="center" width="50px">{{ index+1 }}</td>
                  <td width="300px">{{ t.email }}</td>
                  <td width="50px" align="left">{{ t.country }}</td>
                  <td>{{ t.user_agent }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <div v-else>
      <form @submit.prevent="onSubmit()">
        <pre>
{
  <font color="red" v-if="error">Unauthrize</font>
  Login: {
    username  : <input type="text" class="form--mantap" v-model="form.username">
    password  : <input type="password" class="form--mantap" v-model="form.password">
  },
  <button class="btn--mantap" type="submit">< {{ (isLoading) ? 'Initate...' : 'Login' }} / ></button>
}
        </pre>
      </form>
      </div>
    </div>
    <script>
    var app = new Vue({
      el: '#app',
      data: {
        isLoading: false,
        error: false,
        isLogged: (localStorage.getItem('admin')) ? true : false,
        admin: localStorage.getItem('admin') || false,
        form: {
          username: '',
          password: ''
        },
        data: {
          login: [],
          billing: [],
          card: []
        }
      },
      mounted() {
        if (this.isLogged) {
          this.fetch()
        }
      },
      watch: {
        isLogged(e) {
          if (e) {
            this.fetch()
          }
        }
      },
      methods: {
        async fetch() {
          this.isLoading = true
          await axios.get('/admin/fetch', {
            headers: { token: this.admin }
          }).then((e) => {
            this.data    = e.data
          }).catch(() => {
            localStorage.clear()
            this.isLogged = false
          })
          this.isLoading = false
        },
        async onSubmit() {
          this.isLoading  = true
          try {
            const response = await axios.post('/admin/login', this.form)
            localStorage.setItem('admin', response.data.token)
            this.isLogged = true
            this.admin    = response.data.token
          } catch (e) {
            this.error      = true
          } finally {
            this.isLoading  = false
          }
        }
      }
    })
    </script>
  </body>
</html>
