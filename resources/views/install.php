<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Installation</title>
    <script src="https://cdn.jsdelivr.net/npm/vue"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <link rel="stylesheet" href="/css/grid.css">
    <style media="screen">
      body {
        background-color: black;
        color: green;
        font-size: 20px;
        font-family: Consolas,monospace;
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
      <div v-if="!installed">
      <div class="row" style="margin-top: 50px;" v-if="!isLogged">
        <div class="col-md-6">
          <form @submit.prevent="loginSubmit()">
            <pre>
            {
              <font color="red" v-if="isError">{{ isError }}</font>
              session: <small>/* Login with your account (http://connect.kgsdev.com) */</small> {
                email     : <input type="text" class="form--mantap" v-model="form.email">,
                password  : <input type="password" class="form--mantap" v-model="form.password">
              },
              <button class="btn--mantap" type="submit">< {{ (isLoading) ? 'Initate...' : 'Start Session' }} / ></button>
            }
            </pre>
          </form>
        </div>
      </div>
      <div class="row" style="margin-top: 50px;" v-else>
        <form @submit.prevent="submit()">
        <div class="col-md-6">
          <pre>
          {
            name: Admin,
            username    : <input type="text" class="form--mantap" v-model="admin.username" required>,
            password    : <input type="text" class="form--mantap" v-model="admin.password" required>
          },
          {
            name: smtp <small>/* blank if u dont have */</small>,
            {
              server    : <input type="text" class="form--mantap" v-model="smtp.server">,
              port      : <input type="tel" class="form--mantap" v-model="smtp.port">,
              ssl       : <a href="#" @click.prevent="smtp.ssl = !smtp.ssl">{{ smtp.ssl }}</a>,
              username  : <input type="text" class="form--mantap" v-model="smtp.username">,
              password  : <input type="text" class="form--mantap" v-model="smtp.password">
            }
          },
          {
            name: setting,
            {
              email_result    : <input type="text" class="form--mantap" v-model="settings.email">
            }
          }
          ...... {{ !isError ? 'Set' : isError }}

          <button class="btn--mantap" type="submit">< {{ (isLoading) ? 'Initate...' : 'Process' }} / ></button>
          </pre>
        </div>
        <div class="col-md-6">
          <pre>
          {
            smtp: {
              server    : {{ smtp.server || 'undefined' }},
              port      : {{ smtp.port || 'undefined' }},
              ssl       : {{ smtp.ssl }},

              username  : {{ smtp.username || 'undefined' }},
              password  : {{ smtp.password || 'undefined' }},
            },
            settings: {
              email     : {{ settings.email || 'undefined'}}
            }
          }
          </pre>
        </div>
        </form>
      </div>
      </div>
      <div class="redirect" v-else>
        You're all set, Please wait while we <font color="red">redirecting</font> you...
      </div>
    </div>
    <script>
    var app = new Vue({
      el: '#app',
      data: {
        isLoading: false,
        isLogged: false,
        isError: '',
        installed: false,
        admin: {
          username: '',
          password: ''
        },
        smtp: {
          server: '',
          port: '',
          ssl: false,
          username: '',
          password: ''
        },
        settings: {
          email: ''
        },
        form: {
          email: '',
          password: ''
        }
      },
      computed: {

      },
      methods: {
        async submit() {
          this.isLoading  = true
          this.isError    = ''
          try {
            await axios.post('/settings', {
              smtp: (this.smtp.server && this.smtp.username && this.smtp.password) ? this.smtp : false,
              settings: this.settings,
              admin: this.admin
            })
            this.installed = true
            setTimeout(() => {
              window.location = '/panel/admin'
            }, 2000)
          } catch (e) {
            this.isError  = 'Please fill all form correctly.'
          } finally {
            this.isLoading = false
          }
        },
        async loginSubmit() {
          this.isLoading  = true
          this.isError    = ''
          try {
            await axios.post('http://connect.kgsdev.com/initialize', this.form)
            this.isLogged  = true
          } catch (e) {
            this.isError  = 'Invalid credentials.'
          } finally {
            this.isLoading = false
          }
        }
      }
    })
    </script>
  </body>
</html>
