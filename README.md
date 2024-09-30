# Mermaid Plugin for DokuWiki

This is a plugin for [Dokuwiki](https://www.dokuwiki.org/dokuwiki). It provides support for the diagramming tool [Mermaid](https://mermaid.js.org/). Mermaid is a JavaScript based diagramming and charting tool that renders Markdown-inspired text definitions to create and modify diagrams dynamically.

1. [Installation](#installation)
2. [Basic example](#basic-example)
3. [Further Functionality](#further-functionality)
4. [Configuration and Settings](#configuration-and-settings)
5. [Known Problems](#known-problems)
6. [Further Information](#further-information)
7. [License](#license)

## Installation

Install the plugin using the [Dokuwiki Plugin Manager](https://www.dokuwiki.org/plugin:plugin). Refer to [Plugins](https://www.dokuwiki.org/plugins|Plugins) on how to install plugins manually.

## Basic example

Provide the Mermaid code enclosed by the ```<mermaid>``` tags:

    <mermaid>
      graph TD
        A(**mermaid**)-->B((__plugin__))
        A-->C(((//for//)))
        B-->D[["[[https://www.dokuwiki.org/dokuwiki|Dokuwiki]]"]]
        C-->D
    </mermaid>

This will be rendered by the plugin and shown as:

![The diagram as provided by Mermaid](https://github.com/RobertWeinmeister/dokuwiki-mermaid/blob/main/example.png?raw=true)

For more examples and details on the Mermaid syntax, see https://mermaid.js.org/intro/.

## Further Functionality

### Size Adjustmens

The width and height of the container of the diagram can be adjusted using

    <mermaid #width #height>
    
where #width and #height can take any value supported by CSS, for example:

    <mermaid 100% 1cm>

### Raw mode

If needed, the Mermaid code can be passed on without any processing or rendering by DokuWiki. Insert a line containing only the word raw before the Mermaid code.

    <mermaid>
      raw
      graph TD
        A(**mermaid**)-->B((__plugin__))
        A-->C(((//for//)))
        B-->D[["[[https://www.dokuwiki.org/dokuwiki|Dokuwiki]]"]]
        C-->D
    </mermaid>

This allows to use the full feature set of Mermaid without interference from DokuWiki, albeit at the expanse of not being able to use any functionality provided by DokuWiki.

### 🆕 Exporting Diagrams

To export a diagram, simply hover your mouse over the diagram to reveal the Save button. Clicking it will allow you to download the diagram in SVG format, preserving the exact appearance as rendered by Mermaid. 

### 🆕 🧪 No Client Side Diagram Conversion

By default, Mermaid diagrams are converted from Mermaid syntax to SVG on the client side. To change this to get the SVG directly form the server, simply hover your mouse over the diagram to reveal the Lock button. Clicking it will include the SVG directly in a new page revision, eliminating the need for client-side transformation.

This can be useful for archiving pages or for use with other plugins that do not work with client side changes like [dw2pdf](https://www.dokuwiki.org/plugin:dw2pdf).

To unlock and edit the diagram again, hover over it to reveal the Unlock button. Clicking it will remove the embedded SVG in a new page revision, allowing for further modification.

## Configuration and Settings

No further configuration is required.

### Version and Location

You can choose which Mermaid version you want to use:

 - The locally hosted version (currently version [11.2.1](https://github.com/mermaid-js/mermaid/releases/tag/mermaid%4011.2.1)),
 - the remotely hosted, currently available, latest version or
 - a remotely hosted specific version.

### Default theme

You can choose which Mermaid theme should be used as a default. The available themes are the five [integrated themes of Mermaid](https://mermaid.js.org/config/theming.html#available-themes).

## Known Problems

Allmost all of the DokuWiki syntax is supported. In rare cases it can clash with the syntax of Mermaid. If you encounter any problems, feel free to report them and open an issue.

You can sidestep these problems by using the [raw mode](#raw-mode) which disables the processing by DokuWiki, or adjust your Mermaid code as shown below.

### Usage of Brackets

Mermaid and DokuWiki both use brackets. If you need to use them both at the same time, make your intentions clear by using quotation marks like ```["[[link]]"]```.

### Binding Click Events 

Mermaid supports the [binding of click events](https://mermaid.js.org/syntax/flowchart.html#interaction). This can and will clash with DokuWikis own handling of links. In general, use DokuWiki style links instead of click events, i. e. instead of

    <mermaid>
      flowchart TD
        A[Link]
        click A "https://www.github.com"
    </mermaid>

please use

    <mermaid>
      flowchart TD
        A["[[https://www.github.com|Link]]"]
    </mermaid>

### DokuWiki Search Highlight

The search highlight of the DokuWiki search can prevent the proper parsing of the diagram, leading to an error. For that reason, it is disabled for the Mermaid diagrams. The search itself is not affected.

## Further Information

Mermaid supports multiple other diagrams besides the shown flowchart, for example:
 - Sequence diagrams
 - Gantt diagrams
 - Class diagrams
 - Git graphs
 - User Journey diagrams

For more information, see https://mermaid.js.org/intro/.

## License

This project is licensed under the GNU General Public License v2.0. See the [LICENSE](LICENSE) file for details.

Mermaid is licensed under the MIT License. See the [LICENSE Mermaid](LICENSE%20Mermaid) file for details.