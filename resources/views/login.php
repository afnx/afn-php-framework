@temp_file{header}
    <div class="row mt-5 login-form text-center">
        <div class="col-md-12 blog-main">
            <form class="form-signin" action="login/go" method="post">
                <h1 class="h3 mb-3 font-weight-normal">Test Login Page</h1>
                @temp_file{login_alert}
                <label for="emailOrUsername" class="sr-only">Username or Email address</label>
                <input type="text" id="emailOrUsername" name="emailOrUsername" class="form-control" placeholder="Username or Email" value="@data{email_or_username}" autofocus>
                <label for="password" class="sr-only">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Password">
                @temp_file{captcha}
                <button class="btn btn-lg btn-primary btn-block mt-3" type="submit">Log in</button>
                <div style="text-align: left;">Don't have an account? <br/> <a href="signup">Sign up now!</a></div>
            </form>
        </div>
    </div>
@temp_file{footer}
