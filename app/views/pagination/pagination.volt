<ul class="pagination">
    {% set prvePage = page -1 %}
    
    {% if prvePage > 0 %}
    <li {% if prvePage %} class="disable" {% endif %} >{{ link_to( prvePage,"&laquo;") }}</li>
    {% endif %}
        {% if page >= 10 %}
            {% set num = 10.. 20 %}
        {% else %}
            {% set num = 1..10 %}
        {% endif %}
            {% for i in num  %}
                <li{% if page == i %} class="active" {% endif %}>{{ link_to( i, i) }}</li>
            {% endfor %}
     {% set nextPage = page +1 %}        
     {% if nextPage < 21 %}
        <li{% if nextPage %} class="disable" {% endif %} >{{ link_to( nextPage,"&raquo;" ) }} </li>
    {% endif %}
</ul>