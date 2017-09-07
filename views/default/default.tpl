<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    {% block head %}
      <title>{% block title %}MyTravel{% endblock %}</title>
    {% endblock %}
  </head>
  <body>
    {% block content %}
    <div id="content">
      <article>
        <p>
          It's a page!
        </p>
      </article>
    </div>
    {% endblock %}
    <div id="menu">
      {% block menu %}
      <a href="{{basepath}}" title="Home">
        Home
      </a> - 
      <a href="{{basepath}}/story" title="Stories">
        Stories
      </a> - 
      <a href="{{basepath}}/timeline" title="Timeline">
        Timeline
      </a> - 
      <a href="{{basepath}}/locations" title="Locations">
        Locations
      </a> - 
      <a href="{{basepath}}/about" title="About">
        About
      </a>
      {% endblock %}
    </div>
    {% block footer %}
    <!-- script tags can go here -->
    {% endblock %}
  </body>
</html>