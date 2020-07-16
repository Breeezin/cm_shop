# cm_shop
PHP framework, content management and integrated shop

The shop system presented here is the result of inital work in the early 2000s by a small web design company that is no longer.
It was a work in progress a year or 2 later when I started extending/customising it at the request of a former customer of that small web design company.

The framework is written in PHP (initially v4.x, now fully compatible with v7.4) and exclusively used MySQL as the backend (now mostly compatible with PostgreSQL, although a few queries migh need tweaking, I've stuck mostly to vanilla SQL throughout).  

The framework is MVC style framework, the controller based on 'Actions' in a re-entrant style.  Model and Views are complimented with separate Query components (which works as well as you can imagine, not very well; Queries are integrated tightly into the Models and the line is blurred, distinction meaningless).  

It has it's own advanced Templating engine, which isn't too bad.  The CMS management system was the most advanced I'd seen at the time.  It still holds up well, but obviously the markup is a little dated.  Still, it works well and the shop management (products, categories and orders is very well done).

The one bugbear I've not removed from the code is it's use of serialized data in the database.  It's made transitioning from ISO-8859-1 to UTF quite difficult to do.  Serialized data remains encoded with this charset.

Dashboard/Lookup table administration simple framework with different DB layer.

Over the last 15 years, this frame work has been extended from numerous requirements.

Here are just some of the modules I've written for it.

Order, picking and packing sheet system for the order fulfillment teams.
Multiple vendors, each with their own set of products and different fulfillment teams.
Multiple currencies, support for sending chosen payment currency to a payment gateway that natively charges in that currency.
Advanced country setup with zones for products and countries and ordering rules.
Stock level warnings, order recommendations
Integrated support issue/ticketing for rapid support communication.
Competitor price scraping.
Full audit trail.
Intelligent logging.
Advanced payment gateway integration.
Fuzzy logic matching for flagged/unwanted customers including advanced fingerprinting and use of etags.
Extensive user rights system.
Credit and debit customers.
bitcoin and litecoin (core) charging.
new bootstrap front end
a sticky label post item printing system for fulfillment teams.
many customs reports and declaration printouts.
postal system tracking integration


OK, so I'm uploading this system more as a showcase of what I could integrate into a newer codebase.
Although over the years, it's sold tens of millions of dollars, BTC, euros etc of products, it's evolved to fill a specific requirement and as such I'm not convinced that it's suitable for YOUR shop unless your requirements (hostile userbase, lots of fraud, a lot of integrated business logic that matches that which is integrated) match.
Still, there is a lot of code here to sift through.  Enjoy.

Throughout the code, I've kept an eye on security, but if you spot a vulnerability, especially in an administration module, where you have to be logged in to use it, I've likely ignored it.  Anyway, security through obscurity only works if the code is particularly obscure, but it's worked until now.  There has never been a break in (that I know of, obviously).

Me.
