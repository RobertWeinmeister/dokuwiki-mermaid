# Mermaid Plugin for DokuWiki

This is a plugin for [Dokuwiki](https://www.dokuwiki.org/dokuwiki). It provides support for the diagramming tool [Mermaid](https://mermaid.js.org/). Mermaid is a JavaScript based diagramming and charting tool that renders Markdown-inspired text definitions to create and modify diagrams dynamically.

## Installation

Install the plugin using the [Dokuwiki Plugin Manager](https://www.dokuwiki.org/plugin:plugin). Refer to [Plugins](https://www.dokuwiki.org/plugins|Plugins) on how to install plugins manually.

## Usage

Provide the Mermaid code enclosed by the ```<mermaid>``` tags:

    <mermaid>
      graph TD
        A(**mermaid**)-->B((__plugin__))
        A-->C(((//for//)))
        B-->D[["[[https://www.dokuwiki.org/dokuwiki|Dokuwiki]]"]]
        C-->D
    </mermaid>

This will be rendered by the plugin and shown as:

![alt text](https://github.com/RobertWeinmeister/dokuwiki-mermaid/blob/main/example.png?raw=true)

The width and height of the container of the diagram can be adjusted using

    <mermaid #width #height>
    
where #width and #height can take any value supported by CSS:

    <mermaid 100% 1cm>

For more examples and details on the syntax, see https://mermaid.js.org/intro/.

## Configuration and Settings

There is only one setting. You can choose which Mermaid version you want to use:

 - the locally hosted version
 - the remotely hosted, currently available, latest version
 - a remotely hosted specific version

## Potential problems

The syntax of Mermaid and DokuWiki can clash in rare cases. If, for example, you want a link within a box, make your intensions clear by using quotation marks```["[[link]]"]```. If you encounter other problems, feel free to report them and open an issue.

## Further information

Mermaid supports multiple other diagrams besides the shown flowchart, for example:
 - Sequence diagrams
 - Gantt diagrams
 - Class diagrams
 - Git graphs
 - User Journey diagrams

For more information, see https://mermaid.js.org/intro/.