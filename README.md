# Mermaid Plugin for DokuWiki

This is a plugin for [Dokuwiki](https://www.dokuwiki.org/dokuwiki). It provides support for the diagramming tool [Mermaid](https://mermaid.js.org/). Mermaid is a JavaScript based diagramming and charting tool that renders Markdown-inspired text definitions to create and modify diagrams dynamically.

## Installation

Install the plugin using the [Dokuwiki Plugin Manager](https://www.dokuwiki.org/plugin:plugin). Refer to [Plugins](https://www.dokuwiki.org/plugins|Plugins) on how to install plugins manually.

## Usage

    <mermaid>
    graph TD;
        A-->B;
        A-->C;
        B-->D;
        C-->D;
    </mermaid>

```mermaid
graph TD;
    A-->B;
    A-->C;
    B-->D;
    C-->D;
```

The width and height of the container of the diagram can be adjusted using

    <mermaid #width #height>
    
where #width and #height can take any value supported by CSS:

    <mermaid 100% 1cm>

For more examples and details on the syntax, see https://mermaid.js.org/intro/.

## Further information

Mermaid supports multiple other diagrams besides the shown flowchart:
 - Sequence diagrams
 - Gantt diagrams
 - Class diagrams
 - Git graphs
 - User Journey diagrams

For more information, see https://mermaid.js.org/intro/.