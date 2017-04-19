package main

import (
	"fmt"
	"io/ioutil"
	"regexp"
	"strings"
)

func check(e error) {
	if e != nil {
		panic(e)
	}
}

func main() {
	dat, err := ioutil.ReadFile("./5943255.html")
	check(err)
	cont := string(dat)
	// 去除<pre><p><div>
	re := regexp.MustCompile(`	|　|<p[^>]*?>|</p[^>]*?>|<div[\S\s]+?</div>`)
	cont = re.ReplaceAllString(cont, "")

	// 去除所有尖括号内的HTML代码，并换成换行符
	re, _ = regexp.Compile(`<[\S\s]+?>`)
	cont = re.ReplaceAllString(cont, "\n")

	re, _ = regexp.Compile(`\s{2,}`)
	cont = re.ReplaceAllString(cont, "\n")

	fmt.Print(strings.TrimSpace(cont))
}
