package main

import (
	"github.com/mholt/caddy/caddy/caddymain"
)

func main() {
	caddymain.EnableTelemetry = false
	caddymain.Run()
}
