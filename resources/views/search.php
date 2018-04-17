@temp_file{header}
    <div class="row mt-5">
        <div class="col-md-12 blog-main mb-5">
            <form class="form-inline form-search mt-2 mt-md-0" action="search/go" method="GET" >
                <input class="form-control mr-sm-2" name="search" type="text" value="@data{search}" placeholder="Search documentation" aria-label="Search">
                <button class="btn btn-outline-primary my-2 my-sm-0" type="submit">Search</button>
            </form>
            @temp_file{search_results_show}
          </div>
    </div>
@temp_file{footer}
