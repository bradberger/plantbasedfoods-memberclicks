angular.module("MemberDirectory", [])
    .directive("memberDirectory", MemberDirectoryCtrl)
    .directive("companyDirectory", CompanyDirectoryCtrl)
    .directive("individualAffiliateDirectory", IndividualAffiliateDirectoryCtrl)
    .directive("companyAffiliateDirectory", CompanyAffiliateDirectoryCtrl)
    .filter('startFrom', StartFromFilter)
    .filter("url", FilterURL);

function FilterURL() {
    return function(input) {
        if (!input) {
            return "";
        }
        input = input.toString();
        if (input[input.length-1] === "/") {
            input = input.slice(0, -1);
        }
        if (input.indexOf("http") > -1) {
            return input;
        }
        return "http://"+input;
    }
}

function StartFromFilter() {
    return function(input, start) {
        start = +start; //parse to int
        return input.slice(start);
    }
}

CompanyDirectoryCtrl.$inject = ["$filter"];
function CompanyDirectoryCtrl($filter) {
    return {
        scope: {
            searchable: "<",
            memberType: "<",
            organization: "<"
        },
        template:
            "<p>To see a listing of all active members leave all fields blank. Currently we have {{ members.length }} members.</p>" +
            "<div class='layout-row'>" +
                "<div><select ng-model='searchObj.organization'><option value=''>All Organizations</option><option ng-repeat='o in organizations' ng-value='o'>{{ o }}</option></select></div>" +
                "<div><select ng-model='searchObj.organization_address_state'><option value=''>All States</option><option ng-repeat='s in states' ng-value='s'>{{ s }}</option></select></div>" +
                "<div><select ng-show='searchObj.organization_address_state' ng-model='searchObj.organization_address_city'><option value=''>All Cities</option><option ng-repeat='c in cities' ng-value='c'>{{ c }}</option></select></div>" +
                "<div><input type='search' ng-model='searchText' placeholder='Search'></div>" +
            "</div>" +
            "<div class='layout-row layout-wrap layout-padding layout-align-cetner-center'>" +
                "<a class='organization-card with-image' ng-class='{disabled: !m.website_url}' target='_blank' ng-href='{{ m.website_url | url }}' ng-repeat='m in members | filter:searchText | filter:searchObj | orderBy:\"organization\" | startFrom:currentPage*pageSize | limitTo:pageSize'>" +
                    "<span class='avatar layout-row layout-align-center-center'><span><img ng-src='https://pbfa.memberclicks.net/membership/profile/{{ m.profile_id }}/avatar.jpg'></span></span>" +
                    "<div>" +
                        "<h3>{{ m.organization}}</h3>" +
                        "<p ng-if='m.website_url'>{{ m.website_url }}</p>" +
                    "</div>" +
                "</a>" +
            "</div>" +
            "<div class='member-directory pagination'>" +
                "<button type='button' ng-disabled='!currentPage' ng-click='currentPage=currentPage-1'>Prev</button>" +
                "<button type='button' ng-disabled='$index === currentPage' ng-click='$parent.currentPage = $index' ng-repeat='i in pages track by $index'>{{ $index+1 }}</button>" +
                "<button type='button' ng-disabled='currentPage==numberOfPages-1' ng-click='currentPage=currentPage+1'>Next</button>" +
            "</div>",
        link: MemberDirectoryListCtrl($filter)
    }
}

CompanyAffiliateDirectoryCtrl.$inject = ["$filter"];
function CompanyAffiliateDirectoryCtrl($filter) {
    return {
        scope: {
            searchable: "<",
            memberType: "<",
            organization: "<"
        },
        template:
            "<p>To see a listing of all active members leave all fields blank. Currently we have {{ members.length }} company affiliates.</p>" +
            "<div class='layout-row'>" +
                "<div><select ng-model='searchObj.organization'><option value=''>All Organizations</option><option ng-repeat='o in organizations' ng-value='o'>{{ o }}</option></select></div>" +
                "<div><select ng-model='searchObj.organization_address_state'><option value=''>All States</option><option ng-repeat='s in states' ng-value='s'>{{ s }}</option></select></div>" +
                "<div><select ng-show='searchObj.organization_address_state' ng-model='searchObj.organization_address_city'><option value=''>All Cities</option><option ng-repeat='c in cities' ng-value='c'>{{ c }}</option></select></div>" +
                "<div><input type='search' ng-model='searchText' placeholder='Search'></div>" +
            "</div>" +
            "<div class='layout-row layout-wrap layout-padding layout-align-cetner-center'>" +
                "<a class='organization-card' ng-class='{disabled: !m.website_url}' target='_blank' ng-href='{{ m.website_url | url }}' ng-repeat='m in members | filter:searchText | filter:searchObj | orderBy:\"organization\" | startFrom:currentPage*pageSize | limitTo:pageSize'>" +
                    "<span class='avatar layout-row layout-align-center-center'><span><img ng-src='https://pbfa.memberclicks.net/membership/profile/{{ m.profile_id }}/avatar.jpg'></span></span>" +
                    "<div>" +
                        "<p>{{ m.organization}}</p>" +
                    "</div>" +
                "</a>" +
            "</div>" +
            "<div class='member-directory pagination'>" +
                "<button type='button' ng-disabled='!currentPage' ng-click='currentPage=currentPage-1'>Prev</button>" +
                "<button type='button' ng-disabled='$index === currentPage' ng-click='$parent.currentPage = $index' ng-repeat='i in pages track by $index'>{{ $index+1 }}</button>" +
                "<button type='button' ng-disabled='currentPage==numberOfPages-1' ng-click='currentPage=currentPage+1'>Next</button>" +
            "</div>",
        link: MemberDirectoryListCtrl($filter)
    }
}

IndividualAffiliateDirectoryCtrl.$inject = ["$filter"];
function IndividualAffiliateDirectoryCtrl($filter) {
    return {
        scope: {
            searchable: "<",
            memberType: "<",
            organization: "<"
        },
        template:
            "<p>To see a listing of all active members leave all fields blank. Currently we have {{ members.length }} individual affiliates.</p>" +
            "<div class='layout-row'>" +
                "<div><input type='search' ng-model='searchText' placeholder='Search'></div>" +
            "</div>" +
            "<div class='layout-row layout-wrap layout-padding layout-align-cetner-center'>" +
                "<div class='organization-card' ng-class='{disabled: !m.website_url}' ng-repeat='m in members | filter:searchText | filter:searchObj | orderBy:\"contact_name\" | startFrom:currentPage*pageSize | limitTo:pageSize'>" +
                    "<div>" +
                        "<p>{{ m.contact_name }}</p>" +
                    "</div>" +
                "</div>" +
            "</div>" +
            "<div class='member-directory pagination'>" +
                "<button type='button' ng-disabled='!currentPage' ng-click='currentPage=currentPage-1'>Prev</button>" +
                "<button type='button' ng-disabled='$index === currentPage' ng-click='$parent.currentPage = $index' ng-repeat='i in pages track by $index'>{{ $index+1 }}</button>" +
                "<button type='button' ng-disabled='currentPage==numberOfPages-1' ng-click='currentPage=currentPage+1'>Next</button>" +
            "</div>",
            link: MemberDirectoryListCtrl($filter)
    }
}

MemberDirectoryCtrl.$inject = ["$filter"];
function MemberDirectoryCtrl($filter) {
    return {
        scope: {
            searchable: "<",
            memberType: "<",
            organization: "<"
        },
        template:
            "<div class='members-table-search layout-row' ng-show='searchable'>" +
                "<div><select ng-model='searchObj.member_type'><option value=''>All Member Types</option><option ng-repeat='t in memberTypes' ng-value='t'>{{ t }}</option></select></div>" +
                "<div><select ng-model='searchObj.organization'><option value=''>All Organizations</option><option ng-repeat='o in organizations' ng-value='o'>{{ o }}</option></select></div>" +
                "<div><select ng-model='searchObj.organization_address_state'><option value=''>All States</option><option ng-repeat='s in states' ng-value='s'>{{ s }}</option></select></div>" +
                "<div ng-show='searchObj.organization_address_state'><select ng-model='searchObj.organization_address_city'><option value=''>All Cities</option><option ng-repeat='c in cities' ng-value='c'>{{ c }}</option></select></div>" +
                "<div><input type='search' ng-model='searchText' placeholder='Search'></div>" +
                "<div flex><button type='button' ng-click='reset($event)' style='margin-top: 1px; height: 28px;'>Reset</button></div>" +
            "</div>" +
            "<table class='members-table'>" +
                "<tr><th>Contact Name</th><th>Member Type</th><th>Organization</th><th>Email</th><th>Address</th></tr>" +
                "<tr ng-repeat='m in members | filter:searchObj | filter:searchText'>" +
                    "<td>{{ m.contact_name }}</td><td>{{ m.member_type }}</td>" +
                    "<td><a target='_blank' ng-if='m.website_url' ng-href='{{ m.website_url }}'>{{ m.organization}}</a><span ng-if='!m.website_url'>{{ m.organization }}</span></td>" +
                    "<td><a target='_blank' href='mailto:{{ m.organization_email || m.email_main }}' ng-show='m.organization_email || m.email_main'>{{ m.organization_email || m.email_main }}</a></td>" +
                    "<td><a target='_blank' ng-if='m.organization_address_line_1' href='https://maps.google.com/?q={{ m.organization_address_line_1 }} {{ m.organization_address_city }} {{ m.organization_address_state }} {{ m.organization_address_zip }}'>{{ m.organization_address_line_1 }} {{ m.organization_address_city }} {{ m.organization_address_state }} {{ m.organization_address_zip }}</a></td>" +
                "</tr>" +
            "</table>" +
            "<div class='member-directory pagination'>" +
                "<button type='button' ng-disabled='!currentPage' ng-click='currentPage=currentPage-1'>Prev</button>" +
                "<button type='button' ng-disabled='$index === currentPage' ng-click='$parent.currentPage = $index' ng-repeat='i in pages track by $index'>{{ $index+1 }}</button>" +
                "<button type='button' ng-disabled='currentPage==numberOfPages-1' ng-click='currentPage=currentPage+1'>Next</button>" +
            "</div>",
        link: MemberDirectoryListCtrl($filter)
    }
}

// MemberDirectoryListCtrl is shared by all directives.
function MemberDirectoryListCtrl($filter) {

    return function($scope, $element, $attrs) {
        angular.extend($scope, {
            reset: reset,
            pageSize: 10,
            currentPage: 0,
            numberOfPages: 1
        });

        activate();

        function activate() {
            getMembers();
            reset();
        }

        function getMembers() {
            $scope.members = (window.members || []).filter(function(m) {
                if (!$scope.memberType) {
                    return true;
                }
                return $scope.memberType == m.member_type;
            }).map(function(m) {
                angular.forEach(["organization_address_city", "organization_address_state", "website_url"], function(k) {
                    if ("undefined" === typeof m[k]) {
                        m[k] = "";
                    }
                });
                return m;
            });
        }

        $scope.$watch("searchObj", calcNumberPages, true);
        $scope.$watch("searchText", calcNumberPages);
        $scope.$watch("members", calcNumberPages);

        function calcNumberPages() {
            $scope.currentPage = 0;
            $scope.numberOfPages = Math.ceil($filter("filter")($filter("filter")($scope.members, $scope.searchText), $scope.searchObj).length / $scope.pageSize);
            $scope.pages = new Array($scope.numberOfPages);
        }

        function reset() {
            $scope.searchText = "";
            $scope.searchObj = {
                member_type: $scope.memberType || "",
                organization: $scope.organization || "",
                organization_address_city: "",
                organization_address_state: ""
            };
        }

        // Create a de-duped list of organizations
        $scope.organizations = $scope.members.filter(function(m) {
            return !$scope.memberType || m.member_type == $scope.memberType;
        }).map(function(m) {
            return m.organization;
        }).filter(function(org, index, members) {
            return org && members.indexOf(org) == index;
        });

        // Create a de-duped list of member types
        $scope.memberTypes = $scope.members.filter(function(m) {
            return !$scope.memberType || m.member_type == $scope.memberType;
        }).map(function(m) {
            return m.member_type;
        }).filter(function(type, index, types) {
           return type && types.indexOf(type) == index;
        });

        $scope.states = $scope.members.filter(function(m) {
            return !$scope.memberType || m.member_type == $scope.memberType;
        }).map(function(m) {
            return m.organization_address_state;
        }).filter(function(type, index, types) {
           return type && types.indexOf(type) == index;
        }).sort();

        $scope.$watch("searchObj.organization_address_state", function(val) {
            $scope.searchObj.organization_address_city = "";
            $scope.cities = $scope.members.filter(function(m) {
                if (!val) {
                    return true;
                }
                return m.organization_address_state == val;
            }).map(function(m) {
                return m.organization_address_city;
            }).filter(function(type, index, types) {
               return type && types.indexOf(type) == index;
            }).sort();
        });
    }
}
