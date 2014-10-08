
jQuery(document).ready(function($){
  var users = $.parseJSON(ca2.users);
  console.log(users);
  var substringMatcher = function(strs) {
    return function findMatches(q, cb) {
      var matches, substrRegex;

      // an array that will be populated with substring matches
      matches = [];

      // regex used to determine if a string contains the substring `q`
      substrRegex = new RegExp(q, 'i');

      // iterate through the pool of strings and for any string that
      // contains the substring `q`, add it to the `matches` array
      $.each(strs, function(i, str) {
        if (substrRegex.test(str.user_name)) {
          // the typeahead jQuery plugin expects suggestions to a
          // JavaScript object, refer to typeahead docs for more info
          matches.push({ value: str.user_name, user_id: str.user_id });
        }
      });

      cb(matches);
    };
  };

  $('#ca2_search_authors .typeahead').typeahead({
    hint: true,
    highlight: true,
    minLength: 1
  },
  {
    name: 'users',
    displayKey: 'value',
    source: substringMatcher(users)
  }).on('typeahead:selected', function(event,data){
    $('#ca2_search_authors').append('<p>'+data.value+'<input type="hidden" value="'+data.user_id+'" name="ca2_post_authors[]"></p>');
    $(this).val('');
  });
});