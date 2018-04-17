@temp_file{header}
    <div class="row mt-5">
        <div class="col-md-12 blog-main">
            <h1>Get Started</h1>
            <p class="bd-lead">This is ready-to-use PHP+MySQL Framework. The framework is designed to make developer's works easier. It comes with Docker to work on localhost.</p>
            <div class="indicator"></div>
            @loop{11}{start}
                    <div class="mb-2">
                        <a href="docs#@data{loop_11_documentation_nav_tag}">@data{loop_11_documentation_nav_title}</a>
                    </div>
                    @loop{22}{start}
                            <div class="mb-1 ml-4">
                                <a href="docs#@data{loop_22_documentation_nav_tag}">@data{loop_22_documentation_nav_title}</a>
                            </div>
                            @loop{33}{start}
                                <div class="mb-2 ml-5">
                                    <a href="docs#@data{loop_33_documentation_nav_tag}">@data{loop_33_documentation_nav_title}</a>
                                </div>
                            @loop{33}{end}
                    @loop{22}{end}
            @loop{11}{end}
            <div class="indicator"></div>
            @loop{1}{start}
                <div id="@data_soft{loop_1_documentation_tag}" class="anchor"></div>
                <div class="mb-5">
                    <div class="mb-4">
                        <h2 class="mb-2">@data_soft{loop_1_documentation_title}</h2>
                        @data_soft{loop_1_documentation_description}
                    </div>

                    @loop{2}{start}
                        <div id="@data_soft{loop_2_documentation_tag}" class="anchor"></div>
                        <div class="mb-3 ml-4">
                            <h4 class="mb-1">@data_soft{loop_2_documentation_title}</h4>
                            <div>@data_soft{loop_2_documentation_description}</div>

                            @loop{3}{start}
                                <div id="@data_soft{loop_3_documentation_tag}" class="anchor"></div>
                                <div class="mb-2 ml-5">
                                    <b>@data_soft{loop_3_documentation_title}</b>
                                    <div>@data_soft{loop_3_documentation_description}</div>
                                </div>
                            @loop{3}{end}
                        </div>
                    @loop{2}{end}
                </div>
            @loop{1}{end}
        </div>
    </div>
@temp_file{footer}
