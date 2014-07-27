;
/*
 {
 "\\UmlGeneratorPhp\\Command\\DotCommand": {
 "file": "/Users/clemens/lib/uml-generator-php/tests/output/src/Command/DotCommand.json",
 "relations": {
 "implement": [],
 "trait": [],
 "extend": "\\Symfony\\Component\\Console\\Command\\Command"
 }
 },
 */
function loadGraph(callbackFunc) {
    d3.json('uml-generator-php.index', function (data) {
        //data = dummy();
        var graph = [];
        var nodes = [];
        var links = [];
        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                var id = key;
                nodes.push({
                    id: id
                });

                if (data[key].relations.extend) {
                    tid = data[key].relations.extend;
                    value = {
                        source: id,
                        target: tid,
                        style: 'extends'
                    };
                    links.push(value);
                }
                if (data[key].relations.implement) {
                    var list = data[key].relations.implement;
                    if (list.length) {
                        for (var i = 0; i < list.length; i++) {
                            tid = list[i];
                            value = {
                                source: id,
                                target: tid,
                                style: 'implements'
                            };
                            links.push(value);
                        }
                    }
                }
                if (data[key].relations.trait) {
                    var list = data[key].relations.trait;
                    if (list.length) {
                        for (var i = 0; i < list.length; i++) {
                            tid = list[i];
                            value = {
                                source: id,
                                target: tid,
                                style: 'trait'
                            };
                            links.push(value);
                        }
                    }
                }

            }
        }

        graph = {
            nodes: nodes,
            links: []
        };

        graph.links = [];
        links.forEach(function (e) {
            // Get the source and target nodes
            var sourceNode = graph.nodes.filter(function (n) {
                    return n.id === e.source;
                })[0],
                targetNode = graph.nodes.filter(function (n) {
                    return n.id === e.target;
                })[0];

            if (sourceNode && targetNode) {
                // Add the edge to the array
                graph.links.push({
                    source: sourceNode,
                    target: targetNode,
                    style: e.style
                });
            }
        });
        console.log(graph);
        callbackFunc(graph);
    });

}
;

function dummy() {
    return {
        "A": {
            "file": "A.json",
            "relations": {
                "implement": [],
                "trait": [],
                "extend": "B"
            }
        },
        "B": {
            "file": "B.json",
            "relations": {
                "implement": [],
                "trait": [],
                "extend": "C"
            }
        },
        "C": {
            "file": "C.json",
            "relations": {
                "implement": [''],
                "trait": []
            }
        },
        "I": {
            "file": "D.json",
            "relations": {
                "implement": ['A', 'B'],
                "trait": ['T']
            }
        },
        "T": {
            "file": "D.json",
            "relations": {
                "implement": [],
                "trait": []
            }
        }
    }
}
;