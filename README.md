# MOReader
Reads messages from a gettext .mo file

## Usage
### Basic usage

```php
//Create a reader
$reader = new \Studiow\MOReader\MOReader();

//read a file
$reader->read("path/to/filename.mo"); //returns all messages as an array
```
