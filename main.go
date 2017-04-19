package main

import (
	"database/sql"
	"fmt"
	"github.com/PuerkitoBio/goquery"
	_ "github.com/go-sql-driver/mysql"
	"log"
	"net/url"
	"os"
	"path"
	"regexp"
	"runtime"
	"strconv"
	"strings"
	"time"
)

type ArtClass struct {
	id   int
	name string
	url  string
}

type Article struct {
	id       int
	flid     int
	filename string
	title    string
	url      string
	cont     string
	times    int
}

var (
	chArtCls = make(chan *ArtClass, 2)
	chArts   = make(chan *Article, 50)
	db       *sql.DB
)

func init() {
	dbconfig := fmt.Sprintf("testuser:testpwd@tcp(localhost:3306)/mobiles?charset=utf8&loc=%s&parseTime=true",
		url.QueryEscape("Asia/Shanghai"),
	)

	var err error
	db, err = sql.Open("mysql", dbconfig)
	if err != nil {
		panic(err)
	}

	// 使用前 Ping，确保 DB 连接正常
	err = db.Ping()
	if err != nil {
		panic(err)
	}

	// 设置最大连接数，一定要设置 MaxOpen
	db.SetMaxIdleConns(1000)
	db.SetMaxOpenConns(2000)
}

func run() {
	for {
		select {
		case art := <-chArts:
			go getDetail(art)
		case artCls := <-chArtCls:
			go getList(artCls)
		case <-time.After(2 * time.Second):
			//fmt.Println("timeout")
			time.Sleep(2 * time.Second)
			//default:
			//	time.Sleep(2 * time.Second)
		}
		time.Sleep(10 * time.Millisecond)
	}
}

func UrlConvert(baseUrl string, curUrl string) string {
	u, err := url.Parse(curUrl)
	if err != nil {
		return ""
	}
	base, err := url.Parse(baseUrl)
	if err != nil {
		return ""
	}
	return strings.TrimSpace(base.ResolveReference(u).String())
}

func getList(artCls *ArtClass) {
	sUrl := strings.TrimSpace(artCls.url)
	doc, err := goquery.NewDocument(sUrl)
	if err != nil {
		return
	}

	sel := doc.Selection
	sel.Find("div.rank-switch").Remove()

	sel.Find("div.mod.block.update.chapter-list ul.list li").Each(func(_ int, s *goquery.Selection) {
		go func(s *goquery.Selection, artcls *ArtClass) {
			title := strings.TrimSpace(s.Find("a").Text())
			href, _ := s.Find("a").Attr("href")
			href = strings.TrimSpace(href)
			href = UrlConvert(sUrl, href)

			art := &Article{id: 0, flid: artCls.id, filename: "", title: title, url: href, cont: "", times: 0}
			chArts <- art
			//fmt.Printf("%+v\n", *art)
		}(s, artCls)
	})

	nextUrl, exists := sel.Find("a.nextPage").Attr("href")
	if exists {
		//fmt.Println(exists, sUrl, "next:" ,nextUrl)
		nextUrl = UrlConvert(sUrl, nextUrl)
		artCls.url = nextUrl
		//newArtCls := &ArtClass{artCls.id, artCls.name, nextUrl}
		chArtCls <- artCls
	}
}

func getDetail(art *Article) {
	if art.times > 0 {
		time.Sleep(2 * time.Second)
	}

	if art.times > 2 {
		fmt.Printf("采集失败: %s\n", art.title)
		//fmt.Printf("url: %s\n", art.url)
		return
	}
	dUrl := art.url
	//fmt.Printf("%+v\n", *art)
	//fmt.Printf("%s\n", dUrl)

	doc, err := goquery.NewDocument(dUrl)
	if err != nil {
		return
	}

	cont, err := doc.Find("div.page-content.font-large").Html()
	if err != nil {
		return
	}

	//fmt.Println(cont)
	//art.cont = cont
	//aahtml := cont

	// 去除<pre><p><div>
	//re := regexp.MustCompile(`	|　| |<p[^>]*?>|</p[^>]*?>|<div[\S\s]+?</div>`)
	re := regexp.MustCompile(`	|　| |<p[^>]*?>|</p[^>]*?>|<div[^>]*?>|</div>`)
	cont = re.ReplaceAllString(cont, "")

	// 去除所有尖括号内的HTML代码，并换成换行符
	re, _ = regexp.Compile(`<[\S\s]+?>`)
	cont = re.ReplaceAllString(cont, "\n")

	// 去除报错信息
	re, _ = regexp.Compile(`(?is)【.*?】`)
	cont = re.ReplaceAllString(cont, "")

	// 去除连续的换行符
	re, _ = regexp.Compile(`\s{2,}`)
	cont = re.ReplaceAllString(cont, "\n")

	//cont = art.title + "\n\n" + strings.TrimSpace(cont)
	art.cont = strings.TrimSpace(cont)
	if len(art.cont) == 0 {
		art.times++
		go getDetail(art)
		return
	}

	u, err := url.Parse(dUrl)
	if err != nil {
		return
	}

	filename := "." + strings.TrimSpace(u.Path)
	art.filename = filename
	//SaveToFile(filename, cont)

	fname := strings.TrimSpace(path.Base(filename))
	ext := strings.TrimSpace(path.Ext(filename))
	fname = strings.TrimSpace(strings.Replace(fname, ext, "", -1))
	art.id, _ = strconv.Atoi(fname)

	//SaveToFile("./down/"+fname+ext, aahtml)

	//fmt.Printf("%+v\n", *art)
	go addInfo(art)
}

func addInfo(art *Article) {
	//fmt.Printf("%+v\n", *art)
	//fmt.Println(art.id)

	/*err := db.Ping()
	if err != nil {
		panic(err)
	}*/

	stmt, err := db.Prepare("SELECT id FROM t_article WHERE id = ?")
	if err != nil {
		return
	}
	defer stmt.Close()

	var id int
	err = stmt.QueryRow(art.id).Scan(&id)

	if err == sql.ErrNoRows {
		//fmt.Printf("%+v\n", *art)
		stm, _ := db.Prepare("INSERT INTO t_article(id, flid,filename,title,url,cont) values(?,?,?,?,?,?)")
		defer stm.Close()
		stm.Exec(art.id, art.flid, art.filename, art.title, art.url, art.cont)
		fmt.Printf("采集成功: %s\n", art.title)
	} else {
		//fmt.Println(id)
		//fmt.Printf("重复采集: %s\n", art.title)
		return
	}
}

func SaveToFile(filename, cont string) bool {
	dirname := path.Dir(filename)
	err := os.MkdirAll(dirname, 0755)
	if err != nil {
		log.Println(err)
		return false
	}

	f, err := os.OpenFile(filename, os.O_RDWR|os.O_CREATE, 0666)
	if err != nil {
		return false
	}
	defer f.Close()

	f.WriteString(cont)
	return true
}

func getMoreXS() {
	for {
		stmt, err := db.Prepare("SELECT id,name,muluurl FROM t_art_class WHERE cjflag=0 LIMIT 2")
		if err != nil {
			return
		}
		defer stmt.Close()

		rows, err := stmt.Query()
		if err != nil {
			return
		}
		defer rows.Close()

		for rows.Next() {
			artCls := &ArtClass{}
			err = rows.Scan(&artCls.id, &artCls.name, &artCls.url)
			if err != nil {
				continue
			}
			chArtCls <- artCls

			stm, e := db.Prepare("UPDATE t_art_class SET cjflag=1 WHERE id=?")
			if e != nil {
				continue
			}
			defer stm.Close()
			stm.Exec(artCls.id)
		}

		//fmt.Println("Sleep 2 Seconds")
		time.Sleep(2 * time.Second)
	}
}

func main() {
	runtime.GOMAXPROCS(runtime.NumCPU())

	go run()
	go getMoreXS()

	select {}
}
