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
    
where #width and #height can take any value supported by CSS, for example:

    <mermaid 100% 1cm>

For more examples and details on the Mermaid syntax, see https://mermaid.js.org/intro/.

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

## Configuration and Settings

There is only one setting. You can choose which Mermaid version you want to use:

 - the locally hosted version
 - the remotely hosted, currently available, latest version
 - a remotely hosted specific version

## Known Problems

The syntax of Mermaid and DokuWiki can clash in rare cases. If you encounter problems, feel free to report them and open an issue.

You can sidestep these problems by using the [raw mode](#raw-mode) which disables the processing by DokuWiki, or adjust your Mermaid code as shown below.

### Usage of Brackets

Mermaid and DokuWiki both use brackets. If you need to use them both at the same time, make your intentions clear by using quotation marks like ```["[[link]]"]```.

### Binding Click Events 

Mermaid supports the [binding of click events](https://mermaid.js.org/syntax/flowchart.html#interaction). This can and will clash with DokuWikis own handling of links. Instead of:

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