@temp_file{header}
    <div class="row mt-5 login-form text-center">
        <div class="col-md-12 blog-main">
            <form class="form-signin" action="signup/go" method="post">
                <h1 class="h3 mb-3 font-weight-normal">Test Signup Page</h1>
                @temp_file{signup_alert}
                <label for="full_name" class="sr-only">Email address</label>
                <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Full Name" value="@data{full_name}" autofocus>
                <label for="email" class="sr-only">Email address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="Email Address" value="@data{email}" autofocus>
                <label for="username" class="sr-only">Username</label>
                <input type="username" id="username" name="username" class="form-control" placeholder="Username" value="@data{username}" autofocus>
                <label for="password" class="sr-only">Password</label>
                <input type="password" id="password" name="password" class="form-control" value="@data{password}" placeholder="Password">
                <button class="btn btn-lg btn-primary btn-block mt-3" type="submit">Register</button>
                <div style="text-align: left;">Have an account? <br/> <a href="login">Login.</a></div>
            </form>
        </div>
    </div>
@temp_file{footer}
