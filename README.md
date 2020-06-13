# JATS2Citation
A simple parser for JATS XML for the citation extraction (following the citation rules of PLoS).

## Usage
```php
// Can be a URL or a local XML file
$file = 'https://journals.plos.org/plosone/article/file?id=10.1371/journal.pone.0055937&type=manuscript';

$J2C = new JATS2Citation(); 
$res = $J2C->make_citation($file);

// print the HTML
print $res;
```

... and returns

Torres de la Riva G, Hart BL, Farver TB, Oberbauer AM, Messam LLM, et al. (2013) Neutering Dogs: Effects on Joint Disorders and Cancers in Golden Retrievers. PLoS ONE 8(2): e55937. <a href="https://doi.org/10.1371/journal.pone.0055937" class="cit-url" rel="nofollow">https://doi.org/10.1371/journal.pone.0055937</a>




