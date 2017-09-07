{% extends "default.tpl" %}

{% block title %}{{item.title}}{% endblock %}

{% block content %}
<div id="content">
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
  <aside>
    {% for item in itemList %}
    <a href="{{path('timeline', {title:item.path})}}"><img src="{{path('img', {title:item.path})}}/thumbnail" /> {{item.title}}</a><br>
    {% endfor %}
  </aside>
</div>
{% endblock %}