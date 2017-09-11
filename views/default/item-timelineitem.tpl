{% extends "default.tpl" %}

{% block title %}{{item.title}}{% endblock %}

{% block content %}
<main id="content">
  <article>
    <header>
      <h1>
        {{item.title}}
      </h1>
    </header>
    <figure>
      <img src="{{path('img', {title:item.path})}}" />
      {{item.content}}
    </figure>
  </article>
</main>
<nav>
  {% for item in itemList %}
  <a href="{{path('timeline', {title:item.path})}}"><img src="{{path('img', {title:item.path})}}/thumbnail" /> {{item.title}}</a> 
  {% endfor %}
</nav>
{% endblock %}