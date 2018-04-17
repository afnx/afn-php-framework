<h2 class="mb-3">@data{search_head}</h2>
@loop{1}{start}
    <a href="@data{loop_1_search_link}"><b>@data{loop_1_search_name}</b></a>
    <br/>
    @data{loop_1_search_des} <a href="@data{loop_1_search_link}"><b>more>>></b></a>
    <br/>
    <br/>
@loop{1}{end}

<nav aria-label="...">
  <ul class="pagination">
    @loop{2}{start}
        <li class="page-item">
            <a class="page-link" href="search/go?search=@data{search}&page_number=@data{loop_2_page_number}&tk=@data{tk}">
                @data{loop_2_page_number}
            </a>
        </li>
    @loop{2}{end}
    <li class="page-item active">
      <span class="page-link">
        @data{page_number}
        <span class="sr-only">(current)</span>
      </span>
    </li>
    @loop{3}{start}
        @data{loop_3_dots}
        <li class="page-item">
            <a class="page-link" href="search/go?search=@data{search}&page_number=@data{loop_3_page_number}&tk=@data{tk}">
                @data{loop_3_page_number}
            </a>
        </li>
    @loop{3}{end}
  </ul>
</nav>

<p>Search Time: @data{search_time} | Search Results: @data{search_result_number}</p>