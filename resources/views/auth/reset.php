<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Bootstrap 101 Template</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
</head>
<body>
<div id="app">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">

                <div v-if="resetting" class="panel panel-default" style="margin-top: 48px">
                    <div class="panel-heading" style="background: #FFF;">
                        <h4 class="panel-title">Reset Password</h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="alert alert-info">Reset Password for {{ email }}</div>
                                <div class="form-group" v-bind:class="{ 'has-error': errors['password'] }">
                                    <label class="control-label">New Password</label>
                                    <input v-model="password" type="password" class="form-control" />
                                    <span class="help-block" v-for="item in errors['password']">
                                        {{ item }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <button class="btn btn-primary btn-block" v-on:click="submit()">Submit</button>
                    </div>
                </div>
                <div v-else class="alert alert-success">Your password has been reset.<br />You can now use it to login.</div>
            </div>
        </div>
    </div>
</div>
<script src="https://unpkg.com/vue"></script>
<script src="https://cdn.jsdelivr.net/npm/vue-resource@1.3.4"></script>
<script type="text/javascript">
    var app = new Vue({
        el: '#app',
        data: {
            token: '<?php echo $token ?>',
            email: '<?php echo $email ?>',
            password: '',
            errors: {},
            resetting: true,
        },
        methods: {
            submit: function() {
                this.$http.post('/reset-password', {
                    token: this.token,
                    email: this.email,
                    password: this.password
                }).then(function() {
                    this.resetting = false;
                }, function(response) {
                    if(response.status === 422) {
                        this.errors = response.body.errors;
                    }
                });
            }
        }
    });
    console.log(app);
</script>
</body>
</html>