{% extends "default.tpl" %}

{% block title %}404 You got lost{% endblock %}

{% block head %}
    {{ parent() }}
{% endblock %}

{% block content %}
<main id="content">
  <article>
    <header>
      <h1>Lost 404</h1>
    </header>
    <p>
      You got lost during your travels.<br>
      But hey! The best things can happen when you get lost!
    </p>
  </article>
</main>
{% endblock %}