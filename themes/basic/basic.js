
$(window).load(function(){
	if (navigator.userAgent.toLowerCase().indexOf("chrome") >= 0)
	{
		$('input:-webkit-autofill').each(function(){
			var text = $(this).val();
			var name = $(this).attr('name');
			$(this).after(this.outerHTML).remove();
			$('input[name=' + name + ']').val(text);
		});
	}
});

function appendToNameAndId(element, suffix)
{
	$(element).find('*').each(function() { $(this).attr('id', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : (attr + '-' + suffix)); }); });
	$(element).find('*').each(function() { $(this).attr('for', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : (attr + '-' + suffix)); }); });
	$(element).find('*').each(function() { $(this).attr('name', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : (attr + '-' + suffix)); }); });
	return element;
}

function removeFromNameAndId(element, suffix)
{
	suffix = suffix.replace(/[-[\]{}()*+?.,\\^$|#\s]/g, "\\$&") + '$';
	console.log(suffix);
	$(element).find('*').each(function() { $(this).attr('id', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : attr.replace(new RegExp(suffix), '')); }); });
	$(element).find('*').each(function() { $(this).attr('for', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : attr.replace(new RegExp(suffix), '')); }); });
	$(element).find('*').each(function() { $(this).attr('name', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : attr.replace(new RegExp(suffix), '')); }); });
	return element;
}

function removeTemplate(element)
{
	var ul = $(element).parent().parent();
	$(element).parent().remove();
	addIndexsToCollectionForm(ul);
}

function softRemoveTemplate(element)
{
	var ul = $(element).parent().parent();
	var li = $(element).parent();
	$(li).addClass('removed');
	$(li).find('input, textarea, select, a:not(remove-template)').attr('disabled', 'disabled');
	appendToNameAndId(li, 'removed');
	updateIndexsToCollectionForm(ul);
	$(element).attr('onclick', '').click(function() { reinstateTemplate(element); });
	$(element).attr('title', 'Reinstate this item');
}

function reinstateTemplate(element)
{
	var ul = $(element).parent().parent();
	var li = $(element).parent();
	$(li).removeClass('removed');
	$(li).find('input, textarea, select, a:not(remove-template)').removeAttr('disabled');
	removeFromNameAndId(li, '-removed');
	updateIndexsToCollectionForm(ul);
	$(element).unbind('click');
	$(element).attr('onclick', '').click(function() { softRemoveTemplate(element); });
	$(element).attr('title', 'Remove this item');
}

function addIndexsToCollectionForm(element)
{
	var i = 0;
	$(element).find('li:not(.removed)').each(
			function ()
			{
				$(this).find('*').each(function() { $(this).attr('id', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : attr + '-' + i); }); });
				$(this).find('*').each(function() { $(this).attr('for', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : attr + '-' + i); }); });
				$(this).find('*').each(function() { $(this).attr('name', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : attr + '-' + i); }); });
				i++;
			}
		);
}

function updateIndexsToCollectionForm(element)
{
	var i = 0;
	$(element).find('li:not(.removed)').each(
			function ()
			{
				$(this).find('*').each(function() { $(this).attr('id', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : attr.replace(/^(.*\-)\d+$/, '$1' + i)); }); });
				$(this).find('*').each(function() { $(this).attr('for', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : attr.replace(/^(.*\-)\d+$/, '$1' + i)); }); });
				$(this).find('*').each(function() { $(this).attr('name', function(index, attr) { return (!attr || attr == undefined || attr.length < 1 ? '' : attr.replace(/^(.*\-)\d+$/, '$1' + i)); }); });
				i++;
			}
		);
}

$(document).ready(
		function ()
		{
			$('form .collection-template').each(
					function()
					{
						$(this).find('input[type="button"].template-add').click(
								function ()
								{
									var clone = $(this).parent().find('.new-item-template').html();
									var item_count = $(this).parent().find('ul.templates li').length;
									var new_item = $('<li>' + (clone) + '<a onclick="removeTemplate(this)" class="remove-template" title="Remove this item"></a></li>');
									$(this).parent().find('ul.templates').append(appendToNameAndId(new_item, item_count));
								}
							);
						//console.log($(this).find('ul.templates'));
						addIndexsToCollectionForm($(this).find('ul.templates'));
					}
				);
			$('.collection-template > ul.templates.weighted').sortable({update: function(event, ui) { updateIndexsToCollectionForm(this); }});
			$('select').chosen();
			$('textarea').elastic();
			//$('textarea').resize();
			//$('textarea.resizable:not(.processed)').TextAreaResizer();
			//$('*').load(function() { $('textarea').trigger('update'); } );
		}
	);

$(window).load(
	function ()
	{
		//$('textarea').resize();
		$('textarea').trigger('update');
	}
);
