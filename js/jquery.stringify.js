jQuery.extend({
    stringify: function stringify(obj) {

        switch (typeof(obj)) {
            case 'object':
                var json = [];
                var isArray = (obj && (obj instanceof Array));

                for (var index in obj) {

                    if (obj.hasOwnProperty(index)) {

                        var value = obj[index];

                        switch (typeof(value)) {
                            case 'string':
                                value = '"' + value + '"';
                                break;
                            case 'object':
                                value = (value !== null) ? jQuery.stringify(value) : '';
                                break;
                        }

                        json.push((isArray ? '' : '"' + index + '":') + String(value));
                    }
                }

                return ((isArray ? '[' : '{') + String(json) + (isArray ? ']' : '}'));

            case 'string':
                obj = '"' + obj + '"';
                break;

            default:
                break;
        }

        return String(obj);
    }
});