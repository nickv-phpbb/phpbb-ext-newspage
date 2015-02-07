# Available php Events

### nickvergessen.newspage.newspage
* Locations:
    + controller/main.php
* Variables:
    + `forum_id` - Forum ID of the category to display
    + `year` - Limit the news to a certain year
    + `month` - Limit the news to a certain month
    + `page` - Page to display
* Since: 1.2
* Purpose: You can use this event to load settings on the newspage

### nickvergessen.newspage.single_news
* Locations:
    + controller/main.php
* Variables:
    + `topic_id` - Topic ID of the news to display
* Since: 1.2
* Purpose: You can use this event to load settings on a single page view of the
newspage


# Available Template Events

### nickvergessen_newspage_after
* Locations:
    + styles/prosilver/template/newspage_body.html
* Since: 1.2
* Purpose: Add content after the output of the newspage

### nickvergessen_newspage_before
* Locations:
    + styles/prosilver/template/newspage_body.html
* Since: 1.2
* Purpose: Add content before the output of the newspage

### nickvergessen_newspage_filters_after
* Locations:
    + styles/prosilver/template/newspage_body.html
* Since: 1.2
* Purpose: Add content after the list of filters

### nickvergessen_newspage_filters_before
* Locations:
    + styles/prosilver/template/newspage_body.html
* Since: 1.2
* Purpose: Add content before the list of filters

### nickvergessen_newspage_title_after
* Locations:
    + styles/prosilver/template/newspage_body.html
* Since: 1.2
* Purpose: Add content after the title of the newspage
For before event use `### nickvergessen_newspage_before`

### viewtopic_body_avatar_after
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_avatar_before
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_contact_fields_after
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_contact_fields_before
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_post_author_after
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_post_author_before
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_post_buttons_after
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_post_buttons_before
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_postrow_custom_fields_after
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_postrow_custom_fields_before
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_postrow_post_after
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_postrow_post_before
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_postrow_post_content_footer
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_postrow_post_details_after
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_postrow_post_details_before
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_postrow_post_notices_after
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html

### viewtopic_body_postrow_post_notices_before
* Locations:
    + styles/prosilver/template/viewtopic_postrow.html
* Since: 1.2
* Purpose: Copy the respective event from phpBB's viewtopic_body.html
