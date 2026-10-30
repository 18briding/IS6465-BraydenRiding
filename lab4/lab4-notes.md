PART1: Run the application

1) When the tutor request form is submitted we are redirected to a confirmation page (process-request.php) which shows the parameters selected in the form (course, request details, and topic) along with a status message which says "pending."

2) *Show screenshots*

PART2: Trace the templates

1)the header.php file provides a template for the peer tutor header that can be repeated on multiple pages. It is the top light blue ribbon. It also provides the primary connection the application has to the css.styles sheet.
2)the nav.php file provies a template for the navigation portion of the header where users can click on "requests" or "request a session."
3)the footer.php file provides a template for the white/gray ribbon at the bottom of the webpage that. 
4) the require_once construct allows developers to reference an element from another file mutliple times throughout a web page. With the header, nav, and footer php pages created, using the require_once command allows those elements to be used on any pages with single command. Then if the header needs to be edited, it only needs to be changed once in the header.php file rather than on every single web page.
5)the css.styles sheet is linked to the header file. Since the header is applied on every page of the web application, the css styles sheet will also be applied to every page.

PART3: Observe the form controls

1) reason 
2) topics[]
3) preferredtime
4) preferreddate
5) form_name

Part 4: Test validation behavior

1)
2)
3)

Part 5: Review testing and CRUD pages

1)
2)
3)

