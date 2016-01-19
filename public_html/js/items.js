$(function () {
	$.jstree.defaults.core.themes.variant = "large";
	
	if (typeof window.baseUrl === 'undefined')
	{
		window.baseUrl = '/';
	}	
	
	var to = false;
	$('#item_search').keyup(function () {
		if(to) { clearTimeout(to); }
		to = setTimeout(function () {
			var v = $('#item_search').val();
			$('#jstreeContainer').jstree(true).search(v);
		}, 250);
	});

	$('#jstreeContainer').jstree({
		"core" : {
			"animation" : 1,
			"check_callback" : true,
			"themes" : { "stripes" : true },
			'data' : {
				'url' : function (node) {
					return node.id === '#' ? window.baseUrl + 'root' : window.baseUrl + 'children';
				},
				'data' : function (node) {
					return { 'id' : node.id };
				}
			}
		},
		"types" : {
			"#" : {
				"max_children" : 100,
				"max_depth" : 10,
				"valid_children" : ["item"]
			},
			"item" : {
				"valid_children" : ["item"]
			}
		},
		"plugins" : [
			"unique", "dnd", "search", "wholerow"
		]
	});

	$('#jstreeContainer').bind("loaded.jstree", function (event, data) {
		$(this).jstree("open_all");
	});

	$('#jstreeContainer').bind("create_node.jstree", function (e, data) {
		var node      = $.extend(true, {}, data.node);
		node.parentId = data.node.parent;
		$.ajax({
			type: "POST",
			url: window.baseUrl + 'create',
			data: {
				parent: data.parent,
				name: "New node",
				position: data.position
			},
			dataType: "json",
			success: function(dataFromDb) {
				data.instance.set_id(node,  dataFromDb.id);
				data.instance.edit(dataFromDb.id);
			},
			error: function(req, status, error) {
				alert(req.responseJSON.message);
				window.location = window.location;
			}
		});
	});

	$('#jstreeContainer').bind("rename_node.jstree", function (e, data) {
		$.ajax({
			type: "POST",
			url: window.baseUrl +'rename',
			data: {
				id: data.node.id,
				name: data.text
			},
			dataType: "json",
			success: function(dataFromDb) {},
			error: function(req, status, error) {
				alert(req.responseJSON.message);
				window.location = window.location;
			}
		});
	});

	$('#jstreeContainer').bind("delete_node.jstree", function (e, data) {
		$.ajax({
			type: "POST",
			url: window.baseUrl + 'remove',
			data: { id: data.node.id },
			dataType: "json",
			success: function(dataFromDb) {},
			error: function(req, status, error) {
				alert(req.responseJSON.message);
				window.location = window.location;
			}
		});
	});

	$('#jstreeContainer').bind("move_node.jstree", function (e, data) {
		$.ajax({
			type: "POST",
			url: window.baseUrl + 'move',
			data: {
				id: data.node.id,
				parent: data.parent,
				oldParent: data.old_parent,
				position: data.position,
				oldPosition: data.old_position
			},
			dataType: "json",
			success: function(dataFromDb) {},
			error: function(req, status, error) {
				alert(req.responseJSON.message);
				window.location = window.location;
			}
		});
	});
});

function item_create() {
	var ref = $('#jstreeContainer').jstree(true),
		sel = ref.get_selected();

	if(!sel.length) { return false; }
	sel = sel[0];
	sel = ref.create_node(sel, {"type":"item"});

	if(sel) {
		ref.edit(sel);
	}
}

function item_rename() {
	var ref = $('#jstreeContainer').jstree(true),
		sel = ref.get_selected();

	if(!sel.length) { return false; }

	sel = sel[0]; ref.edit(sel);
}

function item_delete() {
	var ref = $('#jstreeContainer').jstree(true),
		sel = ref.get_selected();

	if(!sel.length) { return false; }

	ref.delete_node(sel);
}