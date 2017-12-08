/* * This is a test comment */

<h2>Template Header</h2>

{header_stuff}

@loop{1}{begin}

// Entry data will be processed here This is content that should be displayed.

@data{test}

@data{test2}

@data{test3}

@loop{1}{end}

<br/><br/>

@loop{2}{begin}

// Entry data will be processed here This is content that should be displayed.

@data{test2}

@data{test3}

@loop{2}{end}

/* * This is another block comment */

Template footer.

{footerStuff}