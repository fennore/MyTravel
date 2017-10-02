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
  {% for listitem in itemList %}
  <a href="{{path('timeline', {title:listitem.path})}}"><img src="{{path('img', {title:listitem.path, trail:'thumbnail'})}}" /> {{listitem.title}}</a> 
  {% endfor %}
</nav>
{% endblock %}