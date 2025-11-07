#include "RoadNetworkSubsystem.h"
#include "RoadNode.h"
#include "RoadSegment.h"

void URoadNetworkSubsystem::RegisterNode(ARoadNode* Node)
{
    if (!Node)
    {
        return;
    }
    CleanupInvalidEntries();
    Nodes.AddUnique(Node);
}

void URoadNetworkSubsystem::UnregisterNode(ARoadNode* Node)
{
    Nodes.Remove(Node);
}

void URoadNetworkSubsystem::RegisterSegment(ARoadSegment* Segment)
{
    if (!Segment)
    {
        return;
    }
    CleanupInvalidEntries();
    Segments.AddUnique(Segment);
}

void URoadNetworkSubsystem::UnregisterSegment(ARoadSegment* Segment)
{
    Segments.Remove(Segment);
}

const TArray<TWeakObjectPtr<ARoadNode>>& URoadNetworkSubsystem::GetNodes() const
{
    return Nodes;
}

const TArray<TWeakObjectPtr<ARoadSegment>>& URoadNetworkSubsystem::GetSegments() const
{
    return Segments;
}

void URoadNetworkSubsystem::CleanupInvalidEntries()
{
    Nodes.RemoveAll([](const TWeakObjectPtr<ARoadNode>& NodePtr)
    {
        return !NodePtr.IsValid();
    });
    Segments.RemoveAll([](const TWeakObjectPtr<ARoadSegment>& SegmentPtr)
    {
        return !SegmentPtr.IsValid();
    });
}
