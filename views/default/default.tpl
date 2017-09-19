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
    <main id="content">
      <article>
        <p>
          It's a page!
        </p>
      </article>
    </main>
    {% endblock %}
    <nav id="menu">
      {% block menu %}
      <a href="{{path('home')}}" title="Home">Home</a> 
      <a href="{{path('story')}}" title="Stories">Stories</a> 
      <a href="{{path('timeline')}}" title="Timeline">Timeline</a> 
      <a href="{{path('locations')}}" title="Locations">Locations</a> 
      <a href="{{path('about')}}" title="About">About</a> 
      {% endblock %}
    </nav>
    {% block footer %}
    <!-- script tags can go here -->
    <script src="https://unpkg.com/vue"></script>
    {% endblock %}
  </body>
</html>