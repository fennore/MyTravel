<!DOCTYPE html>
<html lang="en">
  <head>
    <meta name="viewport" content="initial-scale=1.0">
    <meta charset="utf-8">
    {% block head %}
      <title>{% block title %}Welcome to the story of my Euro-Turkey bicycle tour!{% endblock %}</title>
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
    {% block footer %}
    <!-- script tags can go here -->
    {% endblock %}
  </body>
</html>